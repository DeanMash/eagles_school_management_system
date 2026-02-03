# Timetable Management Guide

## Overview
The timetable system allows administrators to create and manage class schedules. Once created, students and teachers automatically see their respective timetables on their dashboards.

## How Timetables Work

### For Administrators

#### 1. **Access Timetable Management**
- **Location**: Main navigation menu → **"Timetables"** (top-level menu item for admins)
- **Alternative**: Academics → Timetables (for academic staff)

#### 2. **Create a Timetable** (Step-by-Step)

**Step 1: Create Timetable Record**
- Go to Timetables page
- Click "Create Timetable" tab
- Fill in:
  - **Timetable Name**: e.g., "Form 1A Timetable 2026"
  - **Class**: Select the class this timetable is for
  - **Type**: Choose "Regular Class Timetable" or an Exam timetable
- Click "Create Timetable"

**Step 2: Manage Time Slots**
- After creating, find your timetable in the class list
- Click "Manage" from the actions menu
- Go to "Manage Time Slots" tab
- Add time periods (e.g., 8:00 AM - 9:00 AM, 9:00 AM - 10:00 AM)
- You can:
  - Add new time slots manually
  - Copy time slots from another timetable
  - Edit or delete existing time slots

**Step 3: Assign Subjects**
- Still in the "Manage" page
- Go to "Add Subject" tab
- For each day of the week and time slot:
  - Select the day (Monday, Tuesday, etc.)
  - Select the time slot
  - Select the subject
  - Click "Add"
- Repeat for all subjects and time slots

**Step 4: View Complete Timetable**
- Click "View Timetable" to see the complete schedule
- Or go back to the main Timetables page and click "View" for any timetable

#### 3. **Timetable Status Indicators**
- 🟢 **Complete**: Time slots and subjects are assigned
- 🟡 **Needs Subjects**: Time slots exist but subjects need to be added
- 🔴 **Empty**: No time slots created yet

### For Students

Students automatically see their timetable on their dashboard:
- **Today's Schedule**: Shows subjects for today with time indicators
  - 🟢 Current class (happening now)
  - 🟡 Upcoming classes
  - ⚪ Completed classes
- **Weekly Timetable**: Full week view organized by day

**Requirements for Students to See Timetable:**
- Student must be assigned to a class
- A timetable must exist for that class
- Time slots and subjects must be assigned

### For Teachers

Teachers automatically see their timetable on their dashboard:
- **Today's Schedule**: Shows classes they teach today
- **Weekly Timetable**: Full week view of their teaching schedule
- Only shows subjects they are assigned to teach

**Requirements for Teachers to See Timetable:**
- Teacher must be assigned to subjects (via User Management → Teachers)
- Timetables must exist for classes where they teach
- Subjects must be assigned to time slots in those timetables

## Best Practices

1. **Create Timetables Before School Starts**
   - Set up timetables for all classes at the beginning of the term
   - This ensures students and teachers see schedules immediately

2. **Use Descriptive Names**
   - Name timetables clearly: "Form 1A Timetable 2026"
   - Avoid generic names like "Timetable 1"

3. **Complete Setup Before Publishing**
   - Ensure all time slots are added
   - Assign all subjects before students/teachers need to see it
   - Use the status indicators to track completion

4. **Regular vs Exam Timetables**
   - Use "Regular Class Timetable" for weekly schedules
   - Use "Exam Timetable" for exam schedules (select exam from dropdown)

5. **Time Slot Management**
   - Create consistent time slots across classes for easier management
   - Use the "Copy Time Slots" feature to duplicate from another timetable

## Troubleshooting

### Students/Teachers Not Seeing Timetables?

**For Students:**
- ✅ Check student is assigned to a class (Users → Students → Edit)
- ✅ Check timetable exists for that class
- ✅ Check timetable has time slots and subjects assigned
- ✅ Check timetable year matches current session

**For Teachers:**
- ✅ Check teacher is assigned to subjects (Users → Teachers → Edit → Assign Subjects)
- ✅ Check timetables exist for classes where they teach
- ✅ Check subjects are assigned to time slots in those timetables

### Timetable Shows as "Empty"?
- Go to "Manage" → "Manage Time Slots" tab
- Add time slots first
- Then add subjects in "Add Subject" tab

### Can't Assign Subjects?
- Ensure time slots are created first
- Ensure subjects exist for the class (Subjects → Manage Subjects)
- Ensure teachers are assigned to subjects

## Quick Reference

| Action | Location |
|--------|----------|
| Create Timetable | Timetables → Create Timetable tab |
| View Timetables | Timetables → View Timetables → Select Class |
| Manage Time Slots | Timetables → View → Manage → Manage Time Slots |
| Add Subjects | Timetables → View → Manage → Add Subject |
| View Complete Timetable | Timetables → View → View Timetable |
| Edit Timetable Details | Timetables → View → Edit Details |
| Delete Timetable | Timetables → View → Delete (Super Admin only) |

## Technical Details

- **Database Tables**: 
  - `time_table_records`: Main timetable records
  - `time_slots`: Time periods for each timetable
  - `time_tables`: Links subjects to time slots and days

- **Access Control**:
  - Admins (TeamSA): Full access to create, edit, manage, delete
  - Academic Staff: Can view timetables
  - Students: View-only on dashboard
  - Teachers: View-only on dashboard (filtered to their subjects)
