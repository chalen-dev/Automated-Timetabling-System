# Full timetabler (paste-and-run)
# - Enforces session & block exclusivity, PAHF/NSTP/comlab constraints
# - Uses half-hour slots 07:00-21:30 (no 'timeslot' column dependency)
# - Outputs timetables_output.xlsx (openpyxl)
import pandas as pd
import numpy as np
import re, hashlib
from datetime import datetime, timedelta

# ---------- Helpers ----------
def normalize_cols(df):
    df = df.copy()
    df.columns = [re.sub(r'[^0-9a-z]+', '_', str(c).strip().lower()).strip('_') for c in df.columns]
    return df

def parse_hours(s):
    if pd.isna(s): return 2
    s = str(s)
    m = re.search(r'(\d+)', s)
    return int(m.group(1)) if m else 2

def half_hour_slots(start="07:00", end="21:30"):
    fmt="%H:%M"
    t=datetime.strptime(start,fmt); end_t=datetime.strptime(end,fmt)
    out=[]
    while t<=end_t:
        out.append(t.strftime(fmt)); t+=timedelta(minutes=30)
    return out

def slots_needed(hours):
    return int(hours*2)

def make_empty_timetable(index_items, slots):
    df=pd.DataFrame(index=index_items, columns=slots)
    df.index.name='room'; df[:]='vacant'
    return df

def pick_col(df, candidates, fallback=None):
    for c in candidates:
        if c in df.columns: return c
    return fallback

# ---------- Load CSVs (robust) ----------
courses = normalize_cols(pd.read_csv("1st_sem_courses.csv"))
rooms   = normalize_cols(pd.read_csv("rooms.csv"))
# choose session_test_2 if present else session_test_1
try:
    sessions = normalize_cols(pd.read_csv("session_test_2.csv"))
except Exception:
    sessions = normalize_cols(pd.read_csv("session_test_1.csv"))

# ---------- Canonical column mapping ----------
courses = courses.rename(columns={
    pick_col(courses,['session_name','session','sessionname']):'session_name',
    pick_col(courses,['program','degree']):'program',
    pick_col(courses,['year_level','year']):'year_level',
    pick_col(courses,['course_title','title']):'course_title',
    pick_col(courses,['course_name','course']):'course_name',
    pick_col(courses,['class_hours','hours']):'class_hours',
    pick_col(courses,['lecture_days','lec_days']):'lecture_days',
    pick_col(courses,['laboratory_days','lab_days']):'laboratory_days',
    pick_col(courses,['academic_term','term']):'academic_term',
    pick_col(courses,['course_type','type']):'course_type'
})
rooms = rooms.rename(columns={
    pick_col(rooms,['room_name','name','room']):'room_name',
    pick_col(rooms,['type','room_type']):'type',
    pick_col(rooms,['constraints','constraint','notes']):'constraints'
})
sessions = sessions.rename(columns={
    pick_col(sessions,['session_name','session','sessionname']):'session_name',
    pick_col(sessions,['program','degree']):'program',
    pick_col(sessions,['year_level','year']):'year_level',
    pick_col(sessions,['course_title','title']):'course_title',
    pick_col(sessions,['course_name','course']):'course_name',
    pick_col(sessions,['academic_term','term']):'academic_term',
    pick_col(sessions,['course_type','type']):'course_type'
})

# ensure cols exist
for df, cols in [
    (courses, ['session_name','program','year_level','course_title','course_name','class_hours','lecture_days','laboratory_days','academic_term','course_type']),
    (rooms, ['room_name','type','constraints']),
    (sessions, ['session_name','program','year_level','course_title','course_name','academic_term','course_type'])
]:
    for c in cols:
        if c not in df.columns:
            df[c] = np.nan

# ---------- Classify rooms ----------
rooms['room_name'] = rooms['room_name'].astype(str)
rooms['type'] = rooms['type'].fillna('').astype(str).str.lower()
rooms['constraints'] = rooms['constraints'].fillna('').astype(str).str.lower()

rooms['is_gym'] = rooms['room_name'].str.lower().str.startswith('gym') | rooms['constraints'].str.contains('pahf', na=False) | rooms['type'].str.contains('gym', na=False)
rooms['is_comlab'] = rooms['type'].str.contains('comlab|computer|lab', na=False) | rooms['room_name'].str.lower().str.startswith('clv')
rooms['is_nstp'] = rooms['room_name'].str.lower().str.startswith('nstp') | rooms['type'].str.contains('nstp', na=False) | rooms['constraints'].str.contains('nstp', na=False)
rooms['is_lecture'] = ~(rooms['is_comlab'] | rooms['is_gym'] | rooms['is_nstp'])

comlab_rooms = rooms.loc[rooms['is_comlab'],'room_name'].tolist()
gym_rooms = rooms.loc[rooms['is_gym'],'room_name'].tolist()
nstp_rooms = rooms.loc[rooms['is_nstp'],'room_name'].tolist()
lecture_rooms = rooms.loc[rooms['is_lecture'],'room_name'].tolist()

# ---------- Slots & timetables ----------
slots = half_hour_slots("07:00","21:30")
slot_index = {s:i for i,s in enumerate(slots)}
weekdays = ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]
terms = ["T1","T2"]
room_idx = rooms['room_name'].tolist() + ['UNASSIGNED']
timetables = {t: {d: make_empty_timetable(room_idx, slots) for d in weekdays} for t in terms}

# ---------- Merge sessions with courses ----------
merged = sessions.copy()
merged = merged.merge(
    courses[['program','year_level','course_title','course_name','class_hours','lecture_days','laboratory_days','academic_term','course_type']],
    on=['program','year_level','course_title'],
    how='left', suffixes=('','_c')
)

# fallback merge by course_name if needed
if merged['class_hours'].isna().any() and 'course_name' in sessions.columns:
    fallback = sessions.merge(courses[['program','year_level','course_name','class_hours','lecture_days','laboratory_days','academic_term','course_type']].rename(columns={'course_name':'course_name_c'}),
                              left_on=['program','year_level','course_name'],
                              right_on=['program','year_level','course_name_c'],
                              how='left')
    for col in ['class_hours','lecture_days','laboratory_days','academic_term','course_type','course_name']:
        alt = col + '_alt'
        if alt in fallback.columns:
            merged[col] = merged[col].fillna(fallback[alt])

# Defaults & normalize
merged['class_hours'] = merged['class_hours'].fillna('2')
merged['hours'] = merged['class_hours'].apply(parse_hours)
merged['lecture_days'] = merged.get('lecture_days', pd.Series(1,index=merged.index)).fillna(1).astype(int)
merged['laboratory_days'] = merged.get('laboratory_days', pd.Series(0,index=merged.index)).fillna(0).astype(int)
merged['course_type'] = merged.get('course_type','').fillna('').astype(str).str.lower()
merged['academic_term'] = merged.get('academic_term','').fillna('').astype(str).str.lower()
merged['is_semestral'] = merged['academic_term'].str.contains('semes', na=False) | merged['academic_term'].str.contains('semestral', na=False)

# detect PAHF/NSTP robustly
def detect_tags(row):
    title = str(row.get('course_title','') or '').lower()
    name  = str(row.get('course_name','') or '').lower()
    ctype = str(row.get('course_type','') or '').lower()
    is_pahf = bool(re.search(r'\bpahf\b', title)) or bool(re.search(r'\bpahf\b', name)) or ('pahf' in ctype)
    is_nstp = bool(re.search(r'\bnstp\b', title)) or bool(re.search(r'\bnstp\b', name)) or ('nstp' in ctype)
    return pd.Series({'is_pahf':is_pahf, 'is_nstp':is_nstp})

merged[['is_pahf','is_nstp']] = merged.apply(detect_tags, axis=1)

# ---------- Session & block trackers (create AFTER merged) ----------
session_names = [str(s) for s in merged['session_name'].fillna('UNASSIGNED').unique().tolist()]
session_idx = session_names + ['UNASSIGNED']
session_tables = {t: {d: make_empty_timetable(session_idx, slots) for d in weekdays} for t in terms}

def block_id_from_session(sname):
    try:
        parts = str(sname).split('_')
        prog = parts[0].lower()
        year = parts[1].lower()
        grp = parts[2]
        return f"{prog}_{year}_{grp[0].upper()}"
    except:
        return str(sname)

blocks = sorted({ block_id_from_session(s) for s in session_names })
block_idx = blocks + ['UNASSIGNED']
block_tables = {t: {d: make_empty_timetable(block_idx, slots) for d in weekdays} for t in terms}

# ---------- Utility functions for occupancy ----------
def room_free_on_days(term_tables, term, days, room, start_idx, length):
    for d in days:
        df = term_tables[term][d]
        if room not in df.index: return False
        cols = df.columns[start_idx:start_idx+length]
        if any(df.loc[room, cols] != 'vacant'):
            return False
    return True

def session_free_on_days(session_tables_local, term, days, session_name, start_idx, length):
    for d in days:
        df = session_tables_local[term][d]
        if session_name not in df.index:
            continue
        cols = df.columns[start_idx:start_idx+length]
        if any(df.loc[session_name, cols] != 'vacant'):
            return False
    return True

def block_free_on_days(block_tables_local, term, days, block_name, start_idx, length):
    for d in days:
        df = block_tables_local[term][d]
        if block_name not in df.index:
            continue
        cols = df.columns[start_idx:start_idx+length]
        if any(df.loc[block_name, cols] != 'vacant'):
            return False
    return True

def occupy_room_block(term_tables_local, term, days, room, start_idx, length, code):
    for d in days:
        cols = term_tables_local[term][d].columns[start_idx:start_idx+length]
        term_tables_local[term][d].loc[room, cols] = code

def occupy_session_block(session_tables_local, term, days, session_name, start_idx, length, code):
    for d in days:
        cols = session_tables_local[term][d].columns[start_idx:start_idx+length]
        session_tables_local[term][d].loc[session_name, cols] = code

def occupy_block_block(block_tables_local, term, days, block_name, start_idx, length, code):
    for d in days:
        cols = block_tables_local[term][d].columns[start_idx:start_idx+length]
        block_tables_local[term][d].loc[block_name, cols] = code

def pick_least_used_local(term, candidates, usage_dict):
    if not candidates: return None
    return sorted(candidates, key=lambda r: usage_dict[term].get(r,0))[0]

# ---------- Day selection heuristics ----------
def pick_even_days_rotated(k, session_id):
    days_map = weekdays
    n = len(days_map)
    if k <= 0: return []
    if k >= n:
        offset = int(hashlib.md5(str(session_id).encode()).hexdigest(),16) % n
        return [days_map[(i+offset)%n] for i in range(n)]
    base_indices = []
    for i in range(k):
        idx = int(round(i * (n-1) / max(k-1,1))) if k>1 else 0
        base_indices.append(idx)
    offset = int(hashlib.md5(str(session_id).encode()).hexdigest(),16) % n
    rotated = sorted(list(dict.fromkeys([ (bi + offset) % n for bi in base_indices ])))
    return [days_map[i] for i in rotated]

def pick_nonoverlapping_days(lec_days, k, session_id):
    days_map = weekdays
    n = len(days_map)
    if k <= 0: return []
    if not lec_days:
        return pick_even_days_rotated(k, session_id)
    base_indices = []
    for i in range(k):
        idx = int(round(i * (n-1) / max(k-1,1))) if k>1 else 0
        base_indices.append(idx)
    for shift in range(n):
        offset = (int(hashlib.md5(str(session_id).encode()).hexdigest(),16) + shift) % n
        rotated = sorted(list(dict.fromkeys([ (bi + offset) % n for bi in base_indices ])))
        cand = [days_map[i] for i in rotated]
        if set(cand).isdisjoint(set(lec_days)):
            return cand
    return pick_even_days_rotated(k, session_id)

# ---------- Build ordered assignment list (two-phase prioritized) ----------
def parse_group_letter_num(sname):
    try:
        parts = str(sname).split('_'); grp = parts[2]; return grp[0].upper(), int(grp[1:]) if len(grp)>1 else 0
    except: return 'Z',0

merged = merged.copy()
merged['grp_letter'], merged['grp_num'] = zip(*merged.get('session_name', merged.index.astype(str)).map(parse_group_letter_num))

merged['course_key'] = merged.apply(lambda r: (str(r['program']).lower(), str(r['year_level']).lower(), str(r.get('course_name') or r.get('course_title') or '').lower()), axis=1)
course_groups = merged.groupby('course_key')
preferred_sessions = []
for key, grp in course_groups:
    grp_sorted = grp.sort_values(['grp_letter','grp_num'])
    pick = grp_sorted.iloc[0]
    preferred_sessions.append(pick.to_dict())

remaining = merged.copy()
picked_names = {p['session_name'] for p in preferred_sessions if 'session_name' in p}
remaining = remaining[~remaining['session_name'].isin(picked_names)]

remaining_ordered = []
for grp_letter in ['A','B','C']:
    for yr in ['1st','2nd','3rd','4th']:
        for prog in ['bscs','bsit']:
            subset = remaining[(remaining['grp_letter']==grp_letter) & (remaining['year_level']==yr) & (remaining['program'].str.lower()==prog)]
            if subset.empty: continue
            subset_sorted = subset.sort_values(['grp_letter','grp_num'])
            remaining_ordered.extend(subset_sorted.to_dict('records'))

ordered_list = preferred_sessions + remaining_ordered

# ---------- Balancer & windows ----------
room_usage = { t:{r:0 for r in rooms['room_name']} for t in terms }
# PAHF windows (4-hour windows: 07:00, 13:30, 17:30) represented as start slot index and length
pahf_windows = [
    (slot_index.get("07:00"), slots_needed(4)),
    (slot_index.get("13:30"), slots_needed(4)),
    (slot_index.get("17:30"), slots_needed(4))
]

# ---------- Assignment ----------
summary_rows = []
unassigned = []
session_schedule = {}

for rec in ordered_list:
    sname = rec.get('session_name')
    if pd.isna(sname): continue
    sname = str(sname)
    hours = int(rec.get('hours',2))
    needed = slots_needed(hours)
    lec_count = int(rec.get('lecture_days',1))
    lab_count = int(rec.get('laboratory_days',0))
    lec_days = pick_even_days_rotated(lec_count, sname)
    lab_days = pick_nonoverlapping_days(lec_days, lab_count, sname + "_lab")
    acad = str(rec.get('academic_term','') or '').lower()
    is_semestral = bool(rec.get('is_semestral', False))
    is_pahf = bool(rec.get('is_pahf', False))
    is_nstp = bool(rec.get('is_nstp', False))
    # target terms
    if is_semestral:
        target_terms = ['T1','T2']; anchor='T1'
    elif '1st' in acad and '2nd' not in acad:
        target_terms = ['T1']; anchor='T1'
    elif '2nd' in acad and '1st' not in acad:
        target_terms = ['T2']; anchor='T2'
    else:
        target_terms = ['T1']; anchor='T1'
    bid = block_id_from_session(sname)
    assigned_info = {'Lecture':{}, 'Lab':{}}

    # NSTP (Saturday only)
    if is_nstp:
        days_nstp = ['Saturday']
        placed=False
        for st in range(0, len(slots)-needed+1):
            candidates = [r for r in nstp_rooms if room_free_on_days(timetables, anchor, days_nstp, r, st, needed)
                          and session_free_on_days(session_tables, anchor, days_nstp, sname, st, needed)
                          and block_free_on_days(block_tables, anchor, days_nstp, bid, st, needed)]
            if not candidates: continue
            r_anchor = pick_least_used_local(anchor, candidates, room_usage)
            occupy_room_block(timetables, anchor, days_nstp, r_anchor, st, needed, sname); room_usage[anchor][r_anchor]+=1
            occupy_session_block(session_tables, anchor, days_nstp, sname, st, needed, sname)
            occupy_block_block(block_tables, anchor, days_nstp, bid, st, needed, bid)
            # mirror to other target terms
            for other in target_terms:
                if other==anchor: continue
                cand2 = [r for r in nstp_rooms if room_free_on_days(timetables, other, days_nstp, r, st, needed)
                         and session_free_on_days(session_tables, other, days_nstp, sname, st, needed)
                         and block_free_on_days(block_tables, other, days_nstp, bid, st, needed)]
                if cand2:
                    r2 = pick_least_used_local(other, cand2, room_usage); occupy_room_block(timetables, other, days_nstp, r2, st, needed, sname); room_usage[other][r2]+=1
                    occupy_session_block(session_tables, other, days_nstp, sname, st, needed, sname)
                    occupy_block_block(block_tables, other, days_nstp, bid, st, needed, bid)
                else:
                    occupy_room_block(timetables, other, days_nstp, 'UNASSIGNED', st, needed, 'UNASSIGNED')
                    occupy_session_block(session_tables, other, days_nstp, 'UNASSIGNED', st, needed, 'UNASSIGNED')
                    occupy_block_block(block_tables, other, days_nstp, 'UNASSIGNED', st, needed, 'UNASSIGNED')
            for term in target_terms:
                summary_rows.append({'session':sname,'component':'NSTP','term':term,'day':'Saturday','room': (r_anchor if term==anchor else None),'start':slots[st],'end':slots[st+needed-1]})
            placed=True; break
        if not placed:
            unassigned.append({'session':sname,'component':'NSTP','reason':'No NSTP room/time available'})
        session_schedule[sname] = assigned_info
        continue

    # PAHF (gyms, fixed windows)
    if is_pahf:
        placed=False
        for win_start, win_len in pahf_windows:
            if needed > win_len: continue
            rooms_per_day = {}
            target_days = lec_days if lec_days else lab_days if lab_days else ['Monday']
            ok=True
            for d in target_days:
                candidates = [r for r in gym_rooms if room_free_on_days(timetables, anchor, [d], r, win_start, needed)
                              and session_free_on_days(session_tables, anchor, [d], sname, win_start, needed)
                              and block_free_on_days(block_tables, anchor, [d], bid, win_start, needed)]
                if not candidates:
                    ok=False; break
                rooms_per_day[d] = pick_least_used_local(anchor, candidates, room_usage)
            if not ok: continue
            for d,r in rooms_per_day.items():
                occupy_room_block(timetables, anchor, [d], r, win_start, needed, sname); room_usage[anchor][r]+=1
                occupy_session_block(session_tables, anchor, [d], sname, win_start, needed, sname)
                occupy_block_block(block_tables, anchor, [d], bid, win_start, needed, bid)
            for other in target_terms:
                if other==anchor: continue
                for d in rooms_per_day.keys():
                    candidates = [r for r in gym_rooms if room_free_on_days(timetables, other, [d], r, win_start, needed)
                                  and session_free_on_days(session_tables, other, [d], sname, win_start, needed)
                                  and block_free_on_days(block_tables, other, [d], bid, win_start, needed)]
                    if candidates:
                        r2 = pick_least_used_local(other, candidates, room_usage); occupy_room_block(timetables, other, [d], r2, win_start, needed, sname); room_usage[other][r2]+=1
                        occupy_session_block(session_tables, other, [d], sname, win_start, needed, sname)
                        occupy_block_block(block_tables, other, [d], bid, win_start, needed, bid)
                    else:
                        occupy_room_block(timetables, other, [d], 'UNASSIGNED', win_start, needed, 'UNASSIGNED')
                        occupy_session_block(session_tables, other, [d], 'UNASSIGNED', win_start, needed, 'UNASSIGNED')
                        occupy_block_block(block_tables, other, [d], 'UNASSIGNED', win_start, needed, 'UNASSIGNED')
            for term in target_terms:
                for d in rooms_per_day.keys():
                    summary_rows.append({'session':sname,'component':'PAHF','term':term,'day':d,'room': (rooms_per_day.get(d) if term==anchor else None),'start':slots[win_start],'end':slots[win_start+needed-1]})
            placed=True; break
        if not placed:
            win_start, win_len = pahf_windows[0]
            occupy_room_block(timetables, anchor, (lec_days or lab_days or ['Monday']), 'UNASSIGNED', win_start, needed, 'UNASSIGNED')
            occupy_session_block(session_tables, anchor, (lec_days or lab_days or ['Monday']), 'UNASSIGNED', win_start, needed, 'UNASSIGNED')
            occupy_block_block(block_tables, anchor, (lec_days or lab_days or ['Monday']), 'UNASSIGNED', win_start, needed, 'UNASSIGNED')
            unassigned.append({'session':sname,'component':'PAHF','reason':'No gym window available (PAHF locked)'})
        session_schedule[sname] = assigned_info
        continue

    # Lecture assignment
    if lec_days:
        lecture_assigned=False
        for st in range(0, len(slots)-needed+1):
            ok=True; per_day_room={}
            for d in lec_days:
                candidates = [r for r in lecture_rooms if room_free_on_days(timetables, anchor, [d], r, st, needed)]
                candidates = [r for r in candidates if session_free_on_days(session_tables, anchor, [d], sname, st, needed)]
                candidates = [r for r in candidates if block_free_on_days(block_tables, anchor, [d], bid, st, needed)]
                if not candidates:
                    ok=False; break
                per_day_room[d] = pick_least_used_local(anchor, candidates, room_usage)
            if not ok: continue
            for d,r in per_day_room.items():
                occupy_room_block(timetables, anchor, [d], r, st, needed, sname); room_usage[anchor][r]+=1
                occupy_session_block(session_tables, anchor, [d], sname, st, needed, sname)
                occupy_block_block(block_tables, anchor, [d], bid, st, needed, bid)
            for other in target_terms:
                if other==anchor: continue
                for d in lec_days:
                    candidates = [r for r in lecture_rooms if room_free_on_days(timetables, other, [d], r, st, needed)]
                    candidates = [r for r in candidates if session_free_on_days(session_tables, other, [d], sname, st, needed)]
                    candidates = [r for r in candidates if block_free_on_days(block_tables, other, [d], bid, st, needed)]
                    if candidates:
                        r2 = pick_least_used_local(other, candidates, room_usage); occupy_room_block(timetables, other, [d], r2, st, needed, sname); room_usage[other][r2]+=1
                        occupy_session_block(session_tables, other, [d], sname, st, needed, sname)
                        occupy_block_block(block_tables, other, [d], bid, st, needed, bid)
                    else:
                        occupy_room_block(timetables, other, [d], 'UNASSIGNED', st, needed, 'UNASSIGNED')
                        occupy_session_block(session_tables, other, [d], 'UNASSIGNED', st, needed, 'UNASSIGNED')
                        occupy_block_block(block_tables, other, [d], 'UNASSIGNED', st, needed, 'UNASSIGNED')
            for term in target_terms:
                for d in lec_days:
                    summary_rows.append({'session':sname,'component':'Lecture','term':term,'day':d,'room': (per_day_room.get(d) if term==anchor else None),'start':slots[st],'end':slots[st+needed-1]})
            lecture_assigned=True; break
        if not lecture_assigned:
            unassigned.append({'session':sname,'component':'Lecture','reason':'No lecture room/time found'})

    # Lab assignment
    if lab_days:
        lab_assigned=False
        for st in range(0, len(slots)-needed+1):
            ok=True; per_day_lab={}
            for d in lab_days:
                candidates = [r for r in comlab_rooms if room_free_on_days(timetables, anchor, [d], r, st, needed)]
                candidates = [r for r in candidates if session_free_on_days(session_tables, anchor, [d], sname, st, needed)]
                candidates = [r for r in candidates if block_free_on_days(block_tables, anchor, [d], bid, st, needed)]
                if not candidates:
                    ok=False; break
                per_day_lab[d] = pick_least_used_local(anchor, candidates, room_usage)
            if not ok: continue
            for d,r in per_day_lab.items():
                occupy_room_block(timetables, anchor, [d], r, st, needed, sname); room_usage[anchor][r]+=1
                occupy_session_block(session_tables, anchor, [d], sname, st, needed, sname)
                occupy_block_block(block_tables, anchor, [d], bid, st, needed, bid)
            for other in target_terms:
                if other==anchor: continue
                for d in lab_days:
                    candidates = [r for r in comlab_rooms if room_free_on_days(timetables, other, [d], r, st, needed)]
                    candidates = [r for r in candidates if session_free_on_days(session_tables, other, [d], sname, st, needed)]
                    candidates = [r for r in candidates if block_free_on_days(block_tables, other, [d], bid, st, needed)]
                    if candidates:
                        r2 = pick_least_used_local(other, candidates, room_usage); occupy_room_block(timetables, other, [d], r2, st, needed, sname); room_usage[other][r2]+=1
                        occupy_session_block(session_tables, other, [d], sname, st, needed, sname)
                        occupy_block_block(block_tables, other, [d], bid, st, needed, bid)
                    else:
                        occupy_room_block(timetables, other, [d], 'UNASSIGNED', st, needed, 'UNASSIGNED')
                        occupy_session_block(session_tables, other, [d], 'UNASSIGNED', st, needed, 'UNASSIGNED')
                        occupy_block_block(block_tables, other, [d], 'UNASSIGNED', st, needed, 'UNASSIGNED')
            for term in target_terms:
                for d in lab_days:
                    summary_rows.append({'session':sname,'component':'Lab','term':term,'day':d,'room': (per_day_lab.get(d) if term==anchor else None),'start':slots[st],'end':slots[st+needed-1]})
            lab_assigned=True; break
        if not lab_assigned:
            unassigned.append({'session':sname,'component':'Lab','reason':'No comlab available'})

    session_schedule[sname] = {'lec_days':lec_days,'lab_days':lab_days,'target_terms':target_terms}

# ---------- Build wide-format session summary per term ----------
summary_df = pd.DataFrame(summary_rows)
if summary_df.empty:
    wide_T1 = pd.DataFrame(columns=['session']+weekdays)
    wide_T2 = pd.DataFrame(columns=['session']+weekdays)
else:
    summary_df['room'] = summary_df['room'].astype(object).where(summary_df['room'] != 'UNASSIGNED', np.nan)
    comp_priority = {'Lecture':1, 'Lab':2, 'PAHF':3, 'NSTP':4}
    summary_df['comp_pri'] = summary_df['component'].map(comp_priority).fillna(10)
    summary_df.sort_values(['session','term','day','comp_pri'], inplace=True)
    summary_df = summary_df.drop_duplicates(subset=['session','term','day'], keep='first')

    def make_wide(term):
        term_df = summary_df[summary_df['term']==term].copy()
        if term_df.empty:
            return pd.DataFrame(columns=['session']+weekdays)
        pivot = term_df.pivot_table(
            index='session', columns='day', values='room',
            aggfunc=lambda vals: str(next((v for v in vals if pd.notna(v) and str(v)!=''), ""))
        )
        for d in weekdays:
            if d not in pivot.columns:
                pivot[d] = ""
        wide = pivot.reset_index()[['session'] + weekdays]
        return wide

    wide_T1 = make_wide('T1')
    wide_T2 = make_wide('T2')

# ---------- Save outputs ----------
out_file = "timetables_output.xlsx"
with pd.ExcelWriter(out_file, engine='openpyxl') as writer:
    for t in terms:
        for d in weekdays:
            df = timetables[t][d].reset_index().rename(columns={'index':'room'})
            df.to_excel(writer, sheet_name=f"{t}_{d}", index=False)
    if not summary_df.empty:
        summary_df.to_excel(writer, sheet_name='session_summary_long', index=False)
    if not wide_T1.empty:
        wide_T1.to_excel(writer, sheet_name='session_summary_T1', index=False)
    if not wide_T2.empty:
        wide_T2.to_excel(writer, sheet_name='session_summary_T2', index=False)
    pd.DataFrame(unassigned).to_excel(writer, sheet_name='unassigned', index=False)

# ---------- Diagnostics ----------
print("Saved:", out_file)
print("Total sessions processed:", len(ordered_list))
print("Unassigned entries:", len(unassigned))
if len(unassigned)>0:
    print(pd.DataFrame(unassigned).head(20).to_string(index=False))
