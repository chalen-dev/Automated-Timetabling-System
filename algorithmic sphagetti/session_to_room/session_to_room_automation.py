# Colab-ready greedy timetabling script (fixed)
# - Paste this into a single Google Colab cell and run.
# - Requires your CSVs to be in the working directory (or at /content or /mnt/data).
#   Filenames tried: course-sessions.csv or course_sessions.csv,
#                    timetable_template.csv or timetable_template.csv,
#                    timetable-rooms.csv or timetable_rooms.csv

import pandas as pd
import numpy as np
from itertools import combinations
from pathlib import Path
import os

# ---------- utility: find uploaded CSVs ----------
POSSIBLE_BASES = ["/content", "/mnt/data", "."]

def find_existing(filename_variants):
    for base in POSSIBLE_BASES:
        for fname in filename_variants:
            p = Path(base) / fname
            if p.exists():
                return str(p)
    raise FileNotFoundError(f"None of {filename_variants} found in {POSSIBLE_BASES}.")

# ---------- load CSVs (try common variants) ----------
cs_path = find_existing(["course-sessions.csv", "course_sessions.csv"])
rooms_path = find_existing(["timetable-rooms.csv", "timetable_rooms.csv"])
template_path = find_existing(["timetable_template.csv", "timetable-template.csv", "timetable_template.csv"])

print("Loading:")
print(" - course sessions:", cs_path)
print(" - rooms:", rooms_path)
print(" - template:", template_path)

cs = pd.read_csv(cs_path)
rooms = pd.read_csv(rooms_path)
template = pd.read_csv(template_path)

print(f"Course-sessions: {cs.shape}")
print(f"Rooms: {rooms.shape}")
print(f"Template: {template.shape}")

# ---------- parse template times & rooms ----------
time_labels = template.iloc[:,0].astype(str).tolist()
room_cols = list(template.columns[1:])  # these are the column headers for rooms in template

# infer step minutes from first two parseable times
def try_parse_time(s):
    try:
        return pd.to_datetime(str(s))
    except:
        return None

parsed = [try_parse_time(x) for x in time_labels]
parsed = [p for p in parsed if p is not None]
if len(parsed) >= 2:
    step_minutes = int((parsed[1] - parsed[0]).total_seconds() / 60)
else:
    step_minutes = 30
slots_per_day = len(time_labels)
slots_per_hour = int(60 // step_minutes)

# find lunch slot index (slot that starts at 12:00)
lunch_slot_index = None
for i, lbl in enumerate(time_labels):
    low = lbl.lower().replace(" ", "")
    if "12:00" in low or "12:00:00" in low:
        lunch_slot_index = i
        break

print(f"Detected {step_minutes} minute slots, {slots_per_day} slots/day, lunch_slot_index={lunch_slot_index}")

# ---------- normalize rooms df to have 'room_name' & useful cols ----------
if 'room_name' not in rooms.columns:
    # try some common alternatives
    if 'room' in rooms.columns:
        rooms = rooms.rename(columns={'room':'room_name'})
    elif 'room_id' in rooms.columns:
        rooms['room_name'] = rooms['room_id'].astype(str)
    else:
        # fallback: use template room columns as room_name if sizes match
        rooms['room_name'] = rooms.index.to_series().apply(lambda i: room_cols[i] if i < len(room_cols) else f"R{i}")

# build mapping from room_name -> room row (as dict)
rooms_by_name = {str(r['room_name']): r for _, r in rooms.iterrows()}

# warn about template rooms not found in rooms CSV
missing_template_rooms = [r for r in room_cols if r not in rooms_by_name]
if missing_template_rooms:
    print("Warning: the following template room columns are not present in timetable-rooms.csv 'room_name' column:")
    print(missing_template_rooms)
    # still proceed; create dummy room entries for them with default properties
    for r in missing_template_rooms:
        rooms_by_name[r] = {
            'room_name': r,
            'room_type': 'lecture',               # default; user may change later
            'course_type_exclusive_to': 'none',
            'exclusive_day': np.nan
        }

# ---------- helper functions ----------
def session_code(row):
    pa = str(row.get('program_abbreviation','X')).strip().replace(" ", "")
    yl = str(row.get('year_level','X')).strip().replace(" ", "")
    sgid = str(int(row.get('session_group_id',0))) if pd.notna(row.get('session_group_id',None)) else "0"
    csid = str(int(row.get('course_session_id',0))) if pd.notna(row.get('course_session_id',None)) else "0"
    return f"{pa}_{yl}_{sgid}_{csid}"

def room_accepts_session(room_row, session_row):
    # Enforce exclusivity rules:
    cte = str(room_row.get('course_type_exclusive_to','none')).strip().lower()
    ctype = str(session_row.get('course_type','')).strip().lower()
    if cte in ('pe','nstp'):
        # room exclusive -> only accept same course_type
        return ctype == cte
    # major/minor must go to rooms with cte == none
    if ctype in ('major','minor'):
        return cte == 'none' or cte == ''
    # otherwise accept
    return True

def intervals_overlap(start_a, end_a, start_b, end_b):
    # end is exclusive
    return not (end_a <= start_b or end_b <= start_a)

def conflict_with_list(existing_triples, day_idx, start_slot, end_slot):
    # existing_triples: list of (day_idx, s, e)
    for (d, s, e) in existing_triples:
        if d == day_idx and intervals_overlap(start_slot, end_slot, s, e):
            return True
    return False

# ---------- scheduling state: occupancy per term & per room; sg_occupancy per (sgid, term) ----------
terms = ['1st','2nd']
occupancy = {t: {rname: [] for rname in rooms_by_name.keys()} for t in terms}
sg_occupancy = {}  # key: (session_group_id, term) -> list of (day_idx, start_slot, end_slot)

# ---------- prepare course_sessions: numeric conversions & ordering ----------
cs2 = cs.copy()
for c in ['class_hours','total_laboratory_class_days','total_lecture_class_days']:
    if c not in cs2.columns:
        cs2[c] = 0
cs2['class_hours'] = pd.to_numeric(cs2['class_hours'], errors='coerce').fillna(0)
cs2['total_laboratory_class_days'] = pd.to_numeric(cs2['total_laboratory_class_days'], errors='coerce').fillna(0).astype(int)
cs2['total_lecture_class_days'] = pd.to_numeric(cs2['total_lecture_class_days'], errors='coerce').fillna(0).astype(int)
cs2['total_days'] = cs2['total_laboratory_class_days'] + cs2['total_lecture_class_days']
cs2['academic_term'] = cs2['academic_term'].astype(str).str.strip().str.lower()

# ordering per your plotting preference:
sort_cols = []
if 'program_abbreviation' in cs2.columns: sort_cols.append('program_abbreviation')
if 'year_level' in cs2.columns: sort_cols.append('year_level')
sort_cols.append('session_group_id')
cs_sorted = cs2.sort_values(by=sort_cols + ['class_hours','total_days'], ascending=[True]*len(sort_cols) + [False, False]).reset_index(drop=True)

# ---------- main greedy placement ----------
assignments = []   # list of dicts
unassigned = []

for idx, row in cs_sorted.iterrows():
    code = session_code(row)
    academic_term = row['academic_term']
    if academic_term == 'semestral':
        target_terms = ['1st','2nd']   # schedule on 1st then reserve same on 2nd
        schedule_term = '1st'
    elif academic_term == '2nd':
        target_terms = ['2nd']; schedule_term = '2nd'
    else:
        target_terms = ['1st']; schedule_term = '1st'

    slots_needed = int(row['class_hours'] * slots_per_hour)
    total_days = int(row['total_days'])
    num_lec = int(row['total_lecture_class_days'])
    num_lab = int(row['total_laboratory_class_days'])

    # quick sanity checks
    if slots_needed <= 0 or total_days <= 0:
        unassigned.append({'session_code':code, 'reason':'zero slots or zero days', 'row_idx': int(idx)})
        continue
    if slots_needed > slots_per_day:
        unassigned.append({'session_code':code, 'reason':'too long for a day', 'row_idx': int(idx)})
        continue
    placed = False

    # iterate possible start slots (slot indices)
    last_start = slots_per_day - slots_needed
    # build day combinations (Mon..Sat -> 0..5)
    day_indices = range(6)
    day_combos = list(combinations(day_indices, total_days))
    # sort combos by earliest (sum) so that earlier combos are tried first
    day_combos = sorted(day_combos, key=lambda c: (sum(c), c))

    for start_slot in range(0, last_start + 1):
        # lunch rule: disallow intervals that include lunch_slot_index
        if lunch_slot_index is not None:
            if start_slot <= lunch_slot_index < start_slot + slots_needed:
                continue

        for comb in day_combos:
            # define which days are lecture vs lab (first num_lec in comb are lecture)
            lec_days = set(comb[:num_lec]) if num_lec>0 else set()
            lab_days = set(comb[num_lec:num_lec+num_lab]) if num_lab>0 else set()

            # for each day we will pick a room (can be different per day)
            chosen_rooms = {}
            feasible = True

            # iterate days and find a suitable room for each
            for d in comb:
                required_type = 'lecture' if d in lec_days else 'comlab' if d in lab_days else ('lecture' if num_lec>0 else 'comlab')
                found_room = None

                # try template room order first, to respect the template layout
                for rname in room_cols:
                    if rname not in rooms_by_name:
                        continue
                    rrow = rooms_by_name[rname]
                    rtype = str(rrow.get('room_type','')).strip().lower()
                    if rtype != required_type:
                        continue
                    # room exclusivity check
                    if not room_accepts_session(rrow, row):
                        continue
                    # check availability across all target terms
                    ok_all_terms = True
                    for tterm in target_terms:
                        ex_list = occupancy[tterm].get(rname, [])
                        if conflict_with_list(ex_list, d, start_slot, start_slot + slots_needed):
                            ok_all_terms = False
                            break
                    if ok_all_terms:
                        found_room = rname
                        break

                if found_room is None:
                    feasible = False
                    break
                chosen_rooms[d] = found_room

            if not feasible:
                continue

            # check session_group non-overlap across target terms
            sgid = row.get('session_group_id')
            sg_conflict = False
            for tterm in target_terms:
                occ_key = (sgid, tterm)
                existing = sg_occupancy.get(occ_key, [])
                for d in comb:
                    if conflict_with_list(existing, d, start_slot, start_slot + slots_needed):
                        sg_conflict = True
                        break
                if sg_conflict:
                    break
            if sg_conflict:
                continue

            # If we reached here, place the session: reserve slots in occupancy and sg_occupancy
            for tterm in target_terms:
                for d, rname in chosen_rooms.items():
                    occupancy[tterm][rname].append((d, start_slot, start_slot + slots_needed))
                    occ_key = (sgid, tterm)
                    sg_occupancy.setdefault(occ_key, []).append((d, start_slot, start_slot + slots_needed))
                    assignments.append({
                        'session_code': code,
                        'course_session_id': int(row.get('course_session_id', np.nan)),
                        'session_group_id': int(sgid) if pd.notna(sgid) else None,
                        'term': tterm,
                        'day_idx': int(d),
                        'day': ['Mon','Tue','Wed','Thu','Fri','Sat'][d],
                        'start_slot': int(start_slot),
                        'slots': int(slots_needed),
                        'start_time': time_labels[start_slot],
                        'end_time': time_labels[min(start_slot + slots_needed - 1, slots_per_day-1)],
                        'room': rname,
                        'program_abbreviation': row.get('program_abbreviation'),
                        'year_level': row.get('year_level'),
                        'course_title': row.get('course_title')
                    })
            placed = True
            break  # break day_combos loop

        if placed:
            break  # break start_slot loop

    if not placed:
        unassigned.append({'session_code':code, 'course_session_id': int(row.get('course_session_id', np.nan)), 'reason':'no feasible placement'})

# ---------- Export to XLSX with 12 sheets + overview + unassigned ----------
from openpyxl import Workbook
import openpyxl
import math

out_path = "/content/timetables_output.xlsx"
# Build per-term-day DataFrames
days = ['Mon','Tue','Wed','Thu','Fri','Sat']
terms = ['1st','2nd']
sheets = {}
for t in terms:
    for d in days:
        df = pd.DataFrame('vacant', index=time_labels, columns=room_cols)
        sheets[(t,d)] = df

# Fill sheets using assignments
for a in assignments:
    t = a['term']
    d = a['day']
    start = a['start_slot']
    slots = a['slots']
    room = a['room']
    code = a['session_code']
    df = sheets[(t, d)]
    # fill region
    for s in range(start, start + slots):
        if s < 0 or s >= len(time_labels):
            continue
        df.iloc[s, df.columns.get_loc(room)] = code

# Write to Excel
with pd.ExcelWriter(out_path, engine='openpyxl') as writer:
    for t in terms:
        for d in days:
            sheetname = f"{t}_{d}"
            # truncate sheetname if too long
            sheetname = sheetname[:31]
            sheets[(t,d)].to_excel(writer, sheet_name=sheetname)
    # overview: pivot assignments by session_code
    if assignments:
        ov = pd.DataFrame(assignments)
        session_codes = sorted(ov['session_code'].unique())
        rows = []
        for sc in session_codes:
            row = {'session_code': sc}
            sub = ov[ov['session_code']==sc]
            for t in terms:
                for d in days:
                    key = f"{t}_{d}"
                    sel = sub[(sub['term']==t) & (sub['day']==d)]
                    row[key] = ", ".join(sorted(sel['room'].astype(str).unique())) if not sel.empty else "vacant"
            rows.append(row)
        pd.DataFrame(rows).to_excel(writer, sheet_name="overview", index=False)

    if unassigned:
        pd.DataFrame(unassigned).to_excel(writer, sheet_name="unassigned", index=False)

print("Saved timetable workbook to:", out_path)

# ---------- Print summary ----------
print(f"Assignments: {len(assignments)} rows")
print(f"Unassigned sessions: {len(unassigned)}")
if len(unassigned) > 0:
    print("Sample unassigned (first 10):")
    for u in unassigned[:10]:
        print(" -", u)
