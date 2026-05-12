# Information Security Laboratory 2  
## Web Application Security Assessment & Remediation

**Course / Section:** *[fill in]*  
**Student name:** *[fill in]*  
**Student ID:** *[fill in]*  
**Instructor:** *[fill in]*  
**Date submitted:** *[fill in]*  
**Repository link:** *[paste your GitHub URL]*  

---

## 1. Purpose of this document

This document describes the security weaknesses found in the original PHP/MySQL student management system, the risk level of each issue, evidence (screenshots), and the improvements applied to produce a safer version. It also includes a simple backup and recovery plan for the database.

---

## 2. System overview

| Item | Description |
|------|-------------|
| Purpose | Admin logs in, views students, adds students, deletes students |
| Technologies | HTML, CSS, PHP, MySQL (XAMPP) |
| Main files | `login.php`, `dashboard.php`, `add_student.php`, `delete_student.php`, `logout.php`, `db.php`, `style.css` |
| Secured version | Added `auth.php`, `index.php`, updated `infosec_lab.sql` |

---

## 3. Part 1 — Vulnerability assessment

### 3.1 Application-level vulnerabilities

For each row: **what it is**, **why it matters**, **risk**.

---

**1. SQL injection in login (`login.php`)**  
**Risk: Critical**

The original query built a string using values from the form (`$username`, `$password`) inside quotes. An attacker can send crafted input to change the meaning of the SQL (for example, using quotes and `OR` conditions) and possibly log in without a valid password or read data from the database.

---

**2. SQL injection in add student (`add_student.php`)**  
**Risk: Critical**

Student fields were inserted using string concatenation. Malicious input could break out of the string and run extra SQL commands (insert, update, or damage data).

---

**3. SQL injection in delete student (`delete_student.php`)**  
**Risk: Critical**

The `id` from the URL was placed directly into `DELETE FROM students WHERE id=$id`. A value like `1 OR 1=1` could change the query behavior and lead to unauthorized deletion or other SQL abuse.

---

**4. Plaintext passwords in the database**  
**Risk: High**

The `users` table stored passwords as readable text (e.g. `admin123`). Anyone with database access, a leaked backup, or a successful injection could read passwords immediately.

---

**5. No password hashing**  
**Risk: High**

There was no use of `password_hash()` or `password_verify()`, so the system did not follow standard practice for storing credentials.

---

**6. No prepared statements**  
**Risk: High**

User input was mixed into SQL as plain text. Prepared statements separate SQL structure from data and are the standard defense against SQL injection.

---

**7. Weak or missing input validation**  
**Risk: Medium**

There were few checks on length, format, or type of input. That increases abuse surface (unexpected data, errors, and injection payloads).

---

**8. Cross-site scripting (XSS)**  
**Risk: High**

Usernames, names, emails, and other fields were printed with `echo` without escaping. If data contained HTML or script, a browser could run it in the admin’s session (stored XSS after bad data is saved).

---

**9. Broken access control**  
**Risk: High**

`add_student.php` and `delete_student.php` did not verify that the user was logged in. A person could add or delete records without going through login.

---

**10. Insecure direct object reference (IDOR)**  
**Risk: Medium**

Delete used only a numeric `id` in the URL. After adding login, only authenticated users should delete; in a larger system you would also check roles or ownership. The original design had no real access gate.

---

**11. Weak session handling**  
**Risk: Medium**

There was no `session_regenerate_id()` after login (session fixation risk). Logout did not fully clear session cookie data in the hardened pattern.

---

### 3.2 Database-level issues

---

**12. Data redundancy (course fields on every student)**  
**Risk: Medium**

Each student row repeated course name and description. The same course information stored many times wastes space and can become inconsistent (one row says “BSIT”, another spells it differently).

---

**13. No normalization / no separate course entity**  
**Risk: Medium**

Course information belonged in its own table with a foreign key from students, instead of copying text into every student record.

---

**14. No foreign key constraints**  
**Risk: Medium**

Without foreign keys, invalid or “orphan” references are easier; integrity depends only on the application.

---

**15. No encryption of sensitive data at the database layer**  
**Risk: High**

Passwords were not protected. The fix uses application-level hashing (bcrypt via PHP), which is the usual approach for passwords. Full database encryption is optional and was not required for this lab.

---

**16. No backup strategy documented**  
**Risk: High (data loss)**

There was no described plan to export or copy the database. Hardware failure or mistakes could cause permanent loss.

---

### 3.3 Summary table (quick reference)

| # | Vulnerability | Risk |
|---|---------------|------|
| 1 | SQL injection — login | Critical |
| 2 | SQL injection — add student | Critical |
| 3 | SQL injection — delete student | Critical |
| 4 | Plaintext passwords | High |
| 5 | No password hashing | High |
| 6 | No prepared statements | High |
| 7 | Weak input validation | Medium |
| 8 | XSS (no output escaping) | High |
| 9 | Broken access control | High |
| 10 | IDOR-style delete by id | Medium |
| 11 | Weak session / logout | Medium |
| 12 | Redundant course data | Medium |
| 13 | Lack of normalization | Medium |
| 14 | No foreign keys | Medium |
| 15 | Sensitive data not protected | High |
| 16 | No backup plan | High |

*[Minimum 10 vulnerabilities required — this list has 16.]*

---

## 4. Part 2 — Improvements implemented (what changed)

| Area | Before | After |
|------|--------|-------|
| Authentication query | String built from POST | Prepared statement + `password_verify()` |
| Password storage | Plain text | `password_hash()` (bcrypt), column `VARCHAR(255)` |
| Add student | Concatenated INSERT | Prepared INSERT + validation + course must exist |
| Delete student | Raw `id` in SQL | Integer cast + prepared DELETE + login required |
| HTML output | Raw echo | `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` |
| Protected pages | Not all checked | `auth.php` + `require_login()` on dashboard, add, delete |
| Session | Basic | `session_regenerate_id(true)` on login; improved logout |
| Database design | Course text on each student | `courses` table, `students.course_id`, foreign key, unique `student_id` |
| Default URL | No index | `index.php` redirects to `login.php` |

**New or updated files:** `auth.php`, `index.php`, updated `infosec_lab.sql`, updated PHP pages as in the repository.

---

## 5. Before and after (short narrative)

**Before:** The application was suitable for learning but not safe for real use. Attackers could abuse SQL and XSS, passwords were readable, and some actions did not require login. The database repeated course information and had no foreign keys.

**After:** Passwords are hashed and verified with PHP’s built-in functions. All user-controlled values that go into SQL use prepared statements. Output is escaped for the browser. Only logged-in users reach student pages. The schema separates courses from students and uses a foreign key. A basic backup approach is described in Section 7.

---

## 6. Screenshot proof (what to capture)

Paste each image below with a short caption. Replace `[SCREENSHOT]` with your actual image in Word/Google Docs, or print this file from an editor that embeds images.

| Figure | What to show | Your caption |
|--------|----------------|--------------|
| Fig. 1 | Original vulnerable code (optional) — e.g. old `login.php` query with variables inside quotes, if you still have it | *Before: SQL built from user input* |
| Fig. 2 | Browser: login page | *Login page* |
| Fig. 3 | Wrong password → error message (generic) | *Failed login* |
| Fig. 4 | Correct login → dashboard with welcome name | *Successful login* |
| Fig. 5 | phpMyAdmin: `users` row — password column shows long bcrypt string, not plain text | *Hashed password in database* |
| Fig. 6 | phpMyAdmin: Structure of `courses` and `students` showing `course_id` and foreign key | *Normalized tables* |
| Fig. 7 | Add student: invalid email or missing course → validation message | *Input validation* |
| Fig. 8 | Logged out (or private window): open `delete_student.php?id=1` → redirects to login | *Access control on delete* |

---

## 7. Backup and recovery proposal

**Goal:** Be able to restore the `infosec_lab` database after accidental delete, corruption, or machine failure.

**Backup (simple):**

1. On a schedule (e.g. daily), export the database to an `.sql` file using **phpMyAdmin → Export** or the command line:  
   `mysqldump -u root infosec_lab > backup_infosec_lab.sql`
2. Save copies **outside** `htdocs` (for example `C:\Backups\mysql\`).
3. Optionally copy the newest backup to cloud storage or a USB drive.

**Recovery:**

1. Create an empty database named `infosec_lab` (if needed).
2. **Import** the backup `.sql` via phpMyAdmin or:  
   `mysql -u root infosec_lab < backup_infosec_lab.sql`
3. Test recovery on a copy of the project once to confirm the backup opens correctly.

**Good habit:** After major changes, export again so the backup matches the current secured schema.

---

## 8. How to run the secured system (for your instructor)

1. Start **Apache** and **MySQL** in XAMPP.  
2. Create database **`infosec_lab`** (if empty).  
3. Import **`infosec_lab.sql`**.  
4. Open in browser (adjust path to your folder):  
   `http://localhost/.../Infosec_lab2-main/login.php`  
5. Login: **`admin`** / **`admin123`** (change after grading if this is a real server).

---

## 9. Conclusion

This lab identified critical SQL injection issues, weak authentication and session practices, XSS risks, and access control gaps, plus database redundancy and missing integrity controls. The improved version uses hashing, prepared statements, validation, escaping, session hardening, login checks, and a normalized schema with foreign keys. Regular database exports provide a practical backup against data loss.

---

## 10. References (optional)

- OWASP Top 10: https://owasp.org/www-project-top-ten/  
- PHP: `password_hash`, `password_verify`, prepared statements — https://www.php.net/manual/en/book.mysqli.php  

---

*End of documentation. Convert to PDF: open in Microsoft Word / Google Docs / VS Code preview, add screenshots, then File → Save as PDF or Print → Save as PDF.*
