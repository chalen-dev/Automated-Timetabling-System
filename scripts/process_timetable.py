# Colab-ready TIMETABLE (FAST GREEDY) — runtime-relative filepaths
# Upload these CSVs once to Colab runtime (working dir):
#  - course-sessions.csv
#  - session-groups.csv
#  - timetable_template.csv
#  - timetable-professors.csv  (optional)
#  - timetable-rooms.csv
#
#    VENV run command
#   python scripts/process_timetable.py "C:\Users\User\PhpstormProjects\algorithm_driven_website\myapp\storage\app\exports\input-csvs" "C:\Users\User\PhpstormProjects\algorithm_driven_website\myapp\storage\app\exports\timetables" 1
# Output: timetables_output_fast.xlsx (in runtime working folder)

import pandas as pd
import numpy as np
from datetime import datetime, timedelta
from collections import defaultdict, Counter
import itertools
import warnings
import sys
warnings.filterwarnings("ignore")

# ----- CONFIG -----

input_dir = sys.argv[1]        # folder with CSVs
output_dir = sys.argv[2]       # folder to save XLSX
timetable_id = sys.argv[3]     # optional, for naming
OUTPUT_XLSX = f"{output_dir}/{timetable_id}.xlsx"

# CSV paths
COURSE_SESSIONS_CSV = f"{input_dir}/course-sessions.csv"
SESSION_GROUPS_CSV = f"{input_dir}/session-groups.csv"
TEMPLATE_CSV = f"{input_dir}/timetable_template.csv"
ROOMS_CSV = f"{input_dir}/timetable-rooms.csv"

LUNCH_START = "12:00"
LUNCH_END = "12:30"
DAYS = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
# ------------------

def parse_time_label(lbl):
    s = str(lbl).strip()
    fmts = ("%I:%M %p", "%I:%M%p", "%H:%M", "%I:%M %p ")
    for f in fmts:
        try:
            return datetime.strptime(s.lower().replace('.', ''), f)
        except:
            pass
    try:
        return pd.to_datetime(s)
    except Exception as e:
        raise ValueError(f"Cannot parse time label: {lbl} -> {e}")

def pretty_code(row):
    pa = str(row.get("program_abbreviation") or row.get("academic_program") or "UNK").replace(" ", "")
    year = str(row.get("year_level") or "UNK").replace(" ", "")
    sg = str(row.get("session_group_id"))
    csid = str(int(row.get("course_session_id")))
    return f"{pa}_{year}_{sg}_{csid}"

# ---- Load CSVs ----
print("Loading CSVs from runtime folder...")
cs_df = pd.read_csv(COURSE_SESSIONS_CSV)
sg_df = pd.read_csv(SESSION_GROUPS_CSV)
template_df = pd.read_csv(TEMPLATE_CSV)
try:
    prof_df = pd.read_csv(PROFESSORS_CSV)
except:
    prof_df = None
rooms_df = pd.read_csv(ROOMS_CSV)

# normalize column names
cs_df.columns = [c.strip() for c in cs_df.columns]
sg_df.columns = [c.strip() for c in sg_df.columns]
template_df.columns = [c.strip() for c in template_df.columns]
rooms_df.columns = [c.strip() for c in rooms_df.columns]

# ---- Template / timeslots ----
time_col = template_df.columns[0]
time_labels = template_df[time_col].astype(str).tolist()
times_dt = [parse_time_label(t) for t in time_labels]
deltas_min = [(b-a).total_seconds()/60.0 for a,b in zip(times_dt[:-1], times_dt[1:])]
slot_minutes = int(round(np.median(deltas_min))) if len(deltas_min)>0 else 30
slot_hours = slot_minutes / 60.0
total_slots = len(time_labels)
print(f"Detected {total_slots} slots/day, slot length {slot_minutes} minutes")

# ---- Rooms metadata ----
# find room-name column in rooms_df
room_name_col = None
for cand in ("room_name","room_id","name","room"):
    if cand in rooms_df.columns:
        room_name_col = cand
        break
if room_name_col is None:
    raise RuntimeError("Could not find a room name column in timetable-rooms.csv. Expected one of 'room_name','room_id','name','room'.")

rooms_df["_room_key"] = rooms_df[room_name_col].astype(str).str.strip()
rooms_df["course_type_exclusive_to_norm"] = rooms_df.get("course_type_exclusive_to","").fillna("none").astype(str).str.strip().str.lower()
rooms_df["exclusive_days_norm"] = rooms_df.get("exclusive_days","").fillna("").astype(str).str.strip().str.lower()
rooms_df["room_type_norm"] = rooms_df.get("room_type","lecture").fillna("lecture").astype(str).str.strip().str.lower()

room_cols = [c for c in template_df.columns if c != time_col]
# build metadata dict keyed by template room column name
rooms_meta = {}
for rm in room_cols:
    m = rooms_df[rooms_df["_room_key"] == str(rm).strip()]
    if len(m) > 0:
        r = m.iloc[0]
        rooms_meta[rm] = {
            "room_type": r["room_type_norm"],
            "course_type_exclusive_to": r["course_type_exclusive_to_norm"],
            "exclusive_days": r["exclusive_days_norm"]
        }
    else:
        # default if not in rooms csv
        rooms_meta[rm] = {"room_type": "lecture", "course_type_exclusive_to": "none", "exclusive_days": ""}

print("Rooms loaded:", len(room_cols), "lecture/comlab breakdown:",
      Counter([rooms_meta[r]["room_type"] for r in room_cols]))

# ---- Course sessions preprocessing ----
cs = cs_df.copy()
cs["total_laboratory_class_days"] = cs.get("total_laboratory_class_days",0).fillna(0).astype(int)
cs["total_lecture_class_days"] = cs.get("total_lecture_class_days",0).fillna(0).astype(int)
cs["total_days"] = cs["total_laboratory_class_days"] + cs["total_lecture_class_days"]
cs["class_hours"] = cs["class_hours"].astype(float)
cs["required_slots_float"] = cs["class_hours"] / slot_hours
cs["required_slots_round"] = cs["required_slots_float"].round().astype(int)
cs["required_slots_exact"] = (abs(cs["required_slots_float"] - cs["required_slots_round"]) < 1e-6)
if not cs["required_slots_exact"].all():
    print("Warning: some class_hours not exact multiples of slot; rounding to nearest slot.")
cs["required_slots"] = cs["required_slots_round"].astype(int)
cs["course_type_norm"] = cs.get("course_type","").fillna("none").astype(str).str.strip().str.lower()
cs["prog"] = cs.get("program_abbreviation").fillna(cs.get("academic_program"))

# ordering: program, year, session_group, then larger classes first
order_cols = [c for c in ["prog","year_level","session_group_id","required_slots","total_days"] if c in cs.columns]
ordered = cs.sort_values(by=order_cols, ascending=[True, True, True, False, False])

# ---- Availability and occupancy structures ----
availability = {t: {d: {rm: [True]*total_slots for rm in room_cols} for d in DAYS} for t in ["1st","2nd"]}
# apply exclusive_days: room only allowed on that day
for rm, md in rooms_meta.items():
    ex = md.get("exclusive_days","")
    if ex and ex not in ("", "nan"):
        for t in ["1st","2nd"]:
            for d in DAYS:
                if d.lower() != ex.lower():
                    availability[t][d][rm] = [False]*total_slots

session_group_occupancy = {t: {d: defaultdict(lambda: [False]*total_slots) for d in DAYS} for t in ["1st","2nd"]}

assignments = []  # list of assignment blocks

lunch_start_dt = datetime.strptime(LUNCH_START, "%H:%M")
lunch_end_dt = datetime.strptime(LUNCH_END, "%H:%M")
def slot_dt(idx): return times_dt[idx]
def spans_lunch(start, n_slots):
    sdt = slot_dt(start)
    edt = slot_dt(min(start + n_slots, total_slots)-1) + timedelta(minutes=slot_minutes)
    return not (edt <= lunch_start_dt or sdt >= lunch_end_dt)

def room_can_host(rm, course_type, room_type_needed, day):
    md = rooms_meta.get(rm, {})
    if md.get("room_type","").lower() != room_type_needed.lower():
        return False
    ex = md.get("course_type_exclusive_to","none").lower()
    ct = (course_type or "").lower()
    if ct in ("pe","nstp"):
        if ex != ct:
            return False
    else:
        if ex != "none":
            return False
    exday = md.get("exclusive_days","")
    if exday and exday not in ("", "nan"):
        if exday.lower() != day.lower():
            return False
    return True

def available_rooms(term, day, room_type_needed, course_type, start, n_slots):
    rlist = []
    for rm in room_cols:
        if not room_can_host(rm, course_type, room_type_needed, day):
            continue
        if all(availability[term][day][rm][s] for s in range(start, start+n_slots)):
            rlist.append(rm)
    return rlist

def sg_has_conflict(term, sgid, day, start, n_slots):
    occ = session_group_occupancy[term][day][sgid]
    return any(occ[s] for s in range(start, start+n_slots))

# ---- Greedy scheduling (fast) ----
print("Running fast greedy scheduler...")
unassigned = []  # records with reasons

for term in ["1st", "2nd"]:
    for _, row in ordered.iterrows():
        term_val = row["academic_term"]
        # skip 2nd scheduling for semestral (we handle semestral when scheduling 1st)
        if row["academic_term"] == "semestral" and term == "2nd":
            continue
        if row["academic_term"] != "semestral" and row["academic_term"] != term:
            continue

        csid = int(row["course_session_id"])
        code = pretty_code(row)
        course_type = row["course_type_norm"]
        needed_lect = int(row["total_lecture_class_days"])
        needed_lab = int(row["total_laboratory_class_days"])
        n_slots = int(row["required_slots"])
        sgid = row["session_group_id"]

        if n_slots <= 0 or (needed_lect + needed_lab == 0):
            unassigned.append({"course_session_id": csid, "code": code, "term": term, "reason": "invalid_slots_or_zero_days"})
            continue

        placed = False
        # iterate start slots, prefer earlier
        for start in range(0, total_slots - n_slots + 1):
            if spans_lunch(start, n_slots):
                continue

            # identify candidate days that currently have at least one room available (in this TERM)
            lect_days = [d for d in DAYS if len(available_rooms(term, d, "lecture", course_type, start, n_slots)) > 0]
            lab_days = [d for d in DAYS if len(available_rooms(term, d, "comlab", course_type, start, n_slots)) > 0 or len(available_rooms(term, d, "lab", course_type, start, n_slots)) > 0]

            if len(lect_days) < needed_lect or len(lab_days) < needed_lab:
                continue

            lect_combos = [()] if needed_lect==0 else list(itertools.combinations(lect_days, needed_lect))
            lab_combos = [()] if needed_lab==0 else list(itertools.combinations(lab_days, needed_lab))

            success_blocks = None

            for lcombo in lect_combos:
                for labcombo in lab_combos:
                    if set(lcombo).intersection(labcombo):
                        continue
                    # ensure session_group not already busy
                    conflict = False
                    for d in list(lcombo) + list(labcombo):
                        if sg_has_conflict(term, sgid, d, start, n_slots):
                            conflict = True
                            break
                    if conflict:
                        continue

                    # For semestral courses we must ensure same times available in 2nd term too (but rooms can differ).
                    # If current row is semestral, we need to verify that for each day chosen there exists at least one allowed room in BOTH terms.
                    if row["academic_term"] == "semestral":
                        sem_ok = True
                        # check lecture days
                        for d in lcombo:
                            r1 = available_rooms("1st", d, "lecture", course_type, start, n_slots)
                            r2 = available_rooms("2nd", d, "lecture", course_type, start, n_slots)
                            if len(r1)==0 or len(r2)==0:
                                sem_ok = False; break
                        if not sem_ok:
                            continue
                        for d in labcombo:
                            r1 = available_rooms("1st", d, "comlab", course_type, start, n_slots)
                            if len(r1)==0:
                                # maybe 'lab' instead of 'comlab'
                                r1 = available_rooms("1st", d, "lab", course_type, start, n_slots)
                            r2 = available_rooms("2nd", d, "comlab", course_type, start, n_slots)
                            if len(r2)==0:
                                r2 = available_rooms("2nd", d, "lab", course_type, start, n_slots)
                            if len(r1)==0 or len(r2)==0:
                                sem_ok = False; break
                        if not sem_ok:
                            continue

                    # pick first-fit rooms for this TERM (and for semestral also pick rooms for 2nd term)
                    chosen_blocks = []
                    ok = True
                    # lecture rooms
                    for d in lcombo:
                        rlist = available_rooms(term, d, "lecture", course_type, start, n_slots)
                        if not rlist:
                            ok=False; break
                        chosen_blocks.append({"term":term, "day":d, "room":rlist[0], "is_lab":False})
                    if not ok:
                        continue
                    # labs
                    for d in labcombo:
                        rlist = available_rooms(term, d, "comlab", course_type, start, n_slots)
                        if not rlist:
                            rlist = available_rooms(term, d, "lab", course_type, start, n_slots)
                        if not rlist:
                            ok=False; break
                        chosen_blocks.append({"term":term, "day":d, "room":rlist[0], "is_lab":True})
                    if not ok:
                        continue

                    # For semestral: pick rooms for 2nd term (rooms can differ) but must exist — we already checked existence above
                    if row["academic_term"] == "semestral":
                        chosen_blocks_2nd = []
                        for blk in chosen_blocks:
                            d = blk["day"]
                            if blk["is_lab"]:
                                rlist2 = available_rooms("2nd", d, "comlab", course_type, start, n_slots)
                                if not rlist2:
                                    rlist2 = available_rooms("2nd", d, "lab", course_type, start, n_slots)
                            else:
                                rlist2 = available_rooms("2nd", d, "lecture", course_type, start, n_slots)
                            if not rlist2:
                                ok = False; break
                            chosen_blocks_2nd.append({"term":"2nd","day":d,"room":rlist2[0],"is_lab":blk["is_lab"]})
                        if not ok:
                            continue

                    # All checks passed: commit for TERM and for semestral also commit 2nd blocks
                    blocks_to_commit = []
                    for blk in chosen_blocks:
                        blocks_to_commit.append({
                            "course_session_id": csid,
                            "code": code,
                            "term": blk["term"],
                            "day": blk["day"],
                            "room": blk["room"],
                            "start_slot": start,
                            "n_slots": n_slots,
                            "is_lab": blk["is_lab"],
                            "session_group_id": sgid
                        })
                    if row["academic_term"] == "semestral":
                        for blk in chosen_blocks_2nd:
                            blocks_to_commit.append({
                                "course_session_id": csid,
                                "code": code,
                                "term": blk["term"],
                                "day": blk["day"],
                                "room": blk["room"],
                                "start_slot": start,
                                "n_slots": n_slots,
                                "is_lab": blk["is_lab"],
                                "session_group_id": sgid
                            })
                    # commit: update availability and session_group_occupancy
                    for b in blocks_to_commit:
                        assignments.append(b)
                        for s in range(b["start_slot"], b["start_slot"] + b["n_slots"]):
                            availability[b["term"]][b["day"]][b["room"]][s] = False
                            session_group_occupancy[b["term"]][b["day"]][b["session_group_id"]][s] = True

                    success_blocks = blocks_to_commit
                    break
                if success_blocks is not None:
                    break
            if success_blocks is not None:
                placed = True
                break

        if not placed:
            unassigned.append({"course_session_id": csid, "code": code, "term": term, "reason": "no_start_day_room_found"})

print("Greedy scheduling complete. Assigned blocks:", len(assignments), "Unassigned records:", len(unassigned))

# ---- Build output grids and overviews ----
def build_day_grid(term, day):
    grid = template_df.copy(deep=True)
    for rm in room_cols:
        grid[rm] = "vacant"
    grid[time_col] = template_df[time_col]
    for a in assignments:
        if a["term"] != term or a["day"] != day:
            continue
        rm = a["room"]
        start = a["start_slot"]
        n = a["n_slots"]
        for s in range(start, start+n):
            # fill cell if vacant (we guarantee no double-fill for same room/time)
            if grid.at[s, rm] == "vacant":
                grid.at[s, rm] = a["code"]
    return grid

def build_overview(term):
    rows = []
    for _, r in cs.iterrows():
        csid = int(r["course_session_id"])
        code = pretty_code(r)
        rec = {"course_session": code, "course_session_id": csid}
        for d in DAYS:
            matched = [a for a in assignments if a["term"]==term and a["course_session_id"]==csid and a["day"]==d]
            if not matched:
                rec[d] = "vacant"
            else:
                # should be at most 1 booking per day (lecture or lab)
                rec[d] = ";".join(sorted(set([m["room"] for m in matched])))
        rows.append(rec)
    return pd.DataFrame(rows)

# --- Overviews filtered by term and include course_title ---
cs_1st = cs[cs["academic_term"].isin(["1st","semestral"])].copy()
cs_2nd = cs[cs["academic_term"].isin(["2nd","semestral"])].copy()

def build_overview_filtered(term, subset_df):
    rows = []
    for _, r in subset_df.iterrows():
        csid = int(r["course_session_id"])
        code = pretty_code(r)
        title = r.get("course_title", "")
        rec = {
            "course_session": code,
            "course_session_id": csid,
            "course_title": title
        }
        for d in DAYS:
            matched = [a for a in assignments if a["term"]==term and a["course_session_id"]==csid and a["day"]==d]
            if not matched:
                rec[d] = "vacant"
            else:
                rec[d] = ";".join(sorted(set([m["room"] for m in matched])))
        rows.append(rec)
    return pd.DataFrame(rows)

overview_1st = build_overview_filtered("1st", cs_1st)
overview_2nd = build_overview_filtered("2nd", cs_2nd)


# Compose Unassigned final sheet: collapse unique csids with reasons
unq = {}
for u in unassigned:
    cid = int(u["course_session_id"])
    if cid not in unq:
        unq[cid] = {"course_session_id": cid, "code": u.get("code"), "terms_tried": [u.get("term")], "reasons": set([u.get("reason")])}
    else:
        unq[cid]["terms_tried"].append(u.get("term"))
        unq[cid]["reasons"].add(u.get("reason"))
unassigned_list = []
for v in unq.values():
    row = {
        "course_session_id": v["course_session_id"],
        "code": v["code"],
        "terms_tried": ",".join(sorted(set(v["terms_tried"]))),
        "reason": ";".join(sorted(v["reasons"]))
    }
    unassigned_list.append(row)

# ---- Export XLSX ----
print("Writing workbook to", OUTPUT_XLSX)
with pd.ExcelWriter(OUTPUT_XLSX, engine="openpyxl") as w:
    for term in ["1st","2nd"]:
        for d in DAYS:
            sheet = f"{term}_{d[:3]}"
            df_grid = build_day_grid(term, d)
            df_grid.to_excel(w, sheet_name=sheet, index=False)
    overview_1st.to_excel(w, sheet_name="Overview_1st", index=False)
    overview_2nd.to_excel(w, sheet_name="Overview_2nd", index=False)
    if unassigned_list:
        pd.DataFrame(unassigned_list).to_excel(w, sheet_name="Unassigned", index=False)
    else:
        pd.DataFrame([{"note":"No unassigned course_sessions"}]).to_excel(w, sheet_name="Unassigned", index=False)

print("Done. Output file:", OUTPUT_XLSX)
print("Summary: total course_sessions:", len(cs),
      "unique assigned course_session ids:", len(set([a['course_session_id'] for a in assignments])),
      "unique unassigned ids:", len(unassigned_list))
