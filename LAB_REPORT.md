# Infosec Lab 2 — Vulnerability Report & Fixes (Simple)

Use this file as the basis for your PDF: open in a browser or Word, print / Save as PDF.

**Default login after import:** username `admin`, password `admin123`  
**Database:** create empty `infosec_lab` in MySQL, then import `infosec_lab.sql`.

---

## Part 1 — Vulnerabilities Found (10+)

| # | Area | Vulnerability | Risk |
|---|------|---------------|------|
| 1 | App | **SQL injection** in `login.php` (username/password concatenated into query) | **Critical** |
| 2 | App | **SQL injection** in `add_student.php` (INSERT built from raw POST) | **Critical** |
| 3 | App | **SQL injection** in `delete_student.php` (`id` from URL in raw DELETE) | **Critical** |
| 4 | App | **Plaintext passwords** in `users` table (`admin123` stored as plain text) | **High** |
| 5 | App | **No password hashing** (no `password_hash` / `password_verify`) | **High** |
| 6 | App | **No prepared statements** (dynamic SQL strings) | **High** |
| 7 | App | **Little or no input validation** (length, email format, numeric id) | **Medium** |
| 8 | App | **Cross-site scripting (XSS)** risk — session username and DB fields echoed without escaping | **High** |
| 9 | App | **Broken access control** — `add_student.php` and `delete_student.php` had no login check; anyone could add/delete | **High** |
| 10 | App | **Insecure direct object reference** — delete by `?id=` with no ownership or role check | **Medium** |
| 11 | App | **Weak session handling** — no `session_regenerate_id` after login; logout did not clear session cookie fully | **Medium** |
| 12 | DB | **Redundant course data** — `course` and `course_description` duplicated course info on every student row | **Medium** |
| 13 | DB | **No foreign keys** — orphaned or inconsistent `course` text possible | **Medium** |
| 14 | DB | **No encryption** for stored secrets (passwords); relies on app-layer hashing (now added) | **High** |
| 15 | Ops | **No backup / recovery plan** in the original project | **High** (data loss) |

---

## Part 2 — What Was Fixed (Summary)

| Topic | Before | After |
|-------|--------|-------|
| Passwords | Plain text in DB | `password_hash()` / `password_verify()`, `VARCHAR(255)` |
| SQL | String concatenation | **Prepared statements** (`mysqli_prepare` + bind) |
| Input | None | Trims, max lengths, `filter_var` for email, integer id for delete |
| Output | Raw `echo` | **`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`** |
| Sessions | Basic | **`session_regenerate_id(true)`** on successful login; clearer logout |
| Access | Some pages open | **`auth.php` + `require_login()`** on dashboard, add, delete |
| Database | One `students` table with repeated course fields | **`courses` table** + `students.course_id` + **FOREIGN KEY** |
| Student ID | Duplicates allowed | **UNIQUE** on `student_id` |

New file: `auth.php` (session check for protected pages).

---

## Part 3 — Screenshot checklist (you capture these)

1. **Before (optional):** old code or error if you still have a copy — or describe in one line.  
2. **Login:** failed login shows generic message; successful login goes to dashboard.  
3. **phpMyAdmin:** `users.password` shows bcrypt hash, not plain text.  
4. **Tables:** `courses`, `students` with `course_id` and foreign key.  
5. **Add student:** validation message for bad email or empty course.  
6. **Direct URL:** open `delete_student.php?id=1` **without** logging in → redirect to `login.php`.

---

## Backup & recovery (simple proposal)

1. **Daily logical backup:** export the database with `mysqldump` (or phpMyAdmin Export → SQL).  
   Example (adjust path/user):  
   `mysqldump -u root infosec_lab > backup_infosec_lab_%date%.sql`
2. **Store backups** outside the web root (e.g. `C:\backups\mysql\`) and optionally copy to USB/cloud.  
3. **Recovery:** create database `infosec_lab`, then import the `.sql` file (phpMyAdmin Import or `mysql -u root infosec_lab < backup.sql`).  
4. **Test restores** once in a while on a copy so you know the backup works.

---

## Files in the secured project

- `login.php`, `dashboard.php`, `add_student.php`, `delete_student.php`, `logout.php`, `db.php`, `style.css`  
- `auth.php` (new)  
- `infosec_lab.sql` (updated schema + seed user + courses)

---

## GitHub submission reminder

1. Push this folder to your repo.  
2. Include `infosec_lab.sql`.  
3. Export this report to **PDF** for the formal submission.
