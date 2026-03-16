# Quiz Master - CTF Prompt (Medium-Hard)

## Challenge Metadata
- Category: Web Exploitation
- Difficulty: Medium-Hard
- Points: 350
- Type: Multi-Vector SQL Injection
- Author: Vulnix CTF Team
- Flag: VULNIX{5ql_1nj3c710n_m4573r}

## Updated Challenge Description (CTFd)
**Quiz Master**

The Vulnix team built a quiz leaderboard platform for their annual event.
Only administrators can access the control panel, manage scores, and view participant data.
The developers claim the system is production-ready and secure.
But a whistleblower left a note: "They shipped it in 3 hours. No review. No sanitization."
Your goal: Break in. Find the flag. Prove them wrong.

## Story
The lead developer boasted:

"Our admin panel has never been breached. The login is locked down. The search is parameterized. The API is safe."

A whistleblower left this note in Discord:

"Three hours. No code review. No sanitization. They deployed straight to prod."

Your mission is to navigate multiple injection points, escalate privileges, and retrieve the hidden flag from the admin dashboard.

## Application Map
```
http://challenge-ip:8080/
├── index.html
├── login.php
├── search.php
├── profile.php?id=1
├── leaderboard.php
├── admin.php
├── db_connect.php
└── database.db
```

## Database Structure
```sql
CREATE TABLE users (
  id       INTEGER PRIMARY KEY,
  username TEXT,
  password TEXT,
  role     TEXT,
  token    TEXT
);

CREATE TABLE scores (
  id       INTEGER PRIMARY KEY,
  username TEXT,
  score    INTEGER,
  round    TEXT
);

CREATE TABLE flags (
  id    INTEGER PRIMARY KEY,
  name  TEXT,
  value TEXT
);
```

Seed highlights:
- users.admin token: `tok_8f3a91bc`
- flags.admin_flag value: `VULNIX{5ql_1nj3c710n_m4573r}`

## SQL Injection Vectors

### 1) login.php - Classic/Tautology SQLi
Vulnerable query:
```sql
SELECT * FROM users WHERE username='$u' AND password='$p'
```
Payload example:
```text
username: ' OR '1'='1' --
password: anything
```
Outcome:
- Authentication bypass.
- Admin session obtained.
- Admin page asks for token before revealing vault flag.

### 2) search.php - UNION-Based SQLi
Vulnerable query:
```sql
SELECT username, score FROM scores WHERE username LIKE '%$q%'
```
Typical path:
1. Determine column count with ORDER BY.
2. Confirm reflected columns with UNION SELECT.
3. Enumerate tables from sqlite_master.
4. Extract from flags table.

UI constraint:
- Each rendered cell is clipped to 20 characters.
- Players may need `SUBSTR()` to reconstruct longer values.

### 3) profile.php - Blind Boolean SQLi
Vulnerable query:
```sql
SELECT username, role FROM users WHERE id=$id
```
Behavior:
- Role is not rendered.
- Boolean conditions can be used to infer token bytes (true/false page behavior).

## Intended Exploitation Chain
1. login.php: use tautology SQLi to gain admin session.
2. admin.php: observe token-gated vault workflow.
3. profile.php: extract admin token through blind boolean conditions.
4. admin.php: submit token to unlock flag vault.
5. search.php: optional alternate path to extract flag from DB via UNION.

## Recon and Difficulty Layers
- Source recon hint in login HTML comment referencing db_connect.php.
- Leaderboard includes SQLpwner as an in-world hint.
- Admin dashboard includes login anomaly indicator.
- Flag lives in database table, not static admin HTML.
- Multi-vector path supports both intended and creative solves.

## Hint System (CTFd)
- Hint 1 (Free): There is more than one way into a database.
- Hint 2 (50 pts): Login is only one entry point. Review every input.
- Hint 3 (100 pts): ORDER BY helps reveal query structure.
- Hint 4 (150 pts): The flag is stored in a table, not in page source.
- Hint 5 (200 pts): If output is limited, ask true/false questions.
- Hint 6 (250 pts): SQLite supports SUBSTR(text, start, length).

## Docker Deployment
Build and run:
```bash
docker build -t vulnix-quizmaster .
docker run -p 8080:80 vulnix-quizmaster
```

Access:
- http://localhost:8080

## Learning Outcomes
- Classic SQL injection in authentication logic.
- UNION-based extraction and schema discovery.
- Blind boolean inference techniques.
- SQLite internals: sqlite_master, SUBSTR, column matching.
- Trust-boundary failures across multiple endpoints.

## Secure Fix (Post-CTF)
Prepared statements prevent all three vectors by separating SQL code from user data:

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE username=? AND password=?");
$stmt->execute([$u, $p]);
```

```php
$stmt = $pdo->prepare("SELECT username, score FROM scores WHERE username LIKE ?");
$stmt->execute(["%$q%"]);
```

```php
$stmt = $pdo->prepare("SELECT username, role FROM users WHERE id=?");
$stmt->execute([$id]);
```
