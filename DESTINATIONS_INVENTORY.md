# DESTINATIONS INVENTORY REPORT

**Generated:** 2026-02-06  
**Database:** visitor_db

---

## 1. Schema Analysis

### Destinations Table Structure
```sql
CREATE TABLE destinations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL UNIQUE,
  range_start INT NOT NULL,
  range_end INT NOT NULL
);
```

### Tables Referencing Destinations

| Table | Column | FK Type | On Delete |
|-------|--------|---------|-----------|
| `users` | `destination_id` | INT, FK | SET NULL |
| `keycards` | `destination_id` | INT, FK | CASCADE |
| `visitors` | `destination` | VARCHAR(100) | ⚠️ **NO FK** (stores ID as string) |

> [!WARNING]
> The `visitors.destination` column stores the destination **ID as a string**, not as a proper foreign key. This is a data integrity risk.

---

## 2. Full Destination Inventory

| ID | Name | Keycards | Assigned Admins | Visitor Records |
|----|------|----------|-----------------|-----------------|
| 1 | Director's Office | 20 | 0 | 2 |
| 2 | ITeMS Board Secretariat | 20 | 1 | 0 |
| 3 | Gaming Hub | 20 | 1 | 0 |
| 4 | NelFund Support | 20 | 1 | 0 |
| 5 | Directorate's | 20 | 1 | 0 |
| 19 | Directorate | 0 | 1 | 0 |

**Total: 6 destinations**

---

## 3. Duplicate Detection

### Exact Duplicates (normalized_name match)
*None found*

### Near-Duplicates (similar names)

| Group | Destinations | Confidence |
|-------|--------------|------------|
| **"directorate"** | ID 5: "Directorate's", ID 19: "Directorate" | ⚠️ HIGH - likely same office |
| **"director"** | ID 1: "Director's Office", ID 19: "Directorate" | LOW - different concepts |

#### Detailed Analysis: "Directorate" vs "Directorate's"

| Metric | ID 5: "Directorate's" | ID 19: "Directorate" |
|--------|----------------------|---------------------|
| Keycards | 20 (500-519 range) | 0 |
| Assigned Admin | `directorates` (user id 11) | `directorate_admin` (user id 21) |
| Visitor Records | 0 | 0 |
| Keycard Range | 5000-5020 | 6000-6020 |

**Recommendation:** These appear to be duplicates. Merge ID 19 into ID 5.

---

## 4. Merge Plan: "Directorate" → "Directorate's"

### Canonical Destination
- **Keep:** ID 5 ("Directorate's") - has keycards, older ID
- **Remove:** ID 19 ("Directorate") - no keycards, newer ID

### Pre-Merge Verification Queries

```sql
-- 1. Check visitor records referencing ID 19
SELECT COUNT(*) FROM visitors WHERE destination = '19';

-- 2. Check users assigned to ID 19
SELECT id, username, role FROM users WHERE destination_id = 19;

-- 3. Check keycards assigned to ID 19
SELECT COUNT(*) FROM keycards WHERE destination_id = 19;
```

### Merge SQL Statements

```sql
-- ============================================================================
-- MERGE: Move all references from destination 19 to destination 5
-- ============================================================================

-- STEP 1: Update visitor records (if any)
UPDATE visitors SET destination = '5' WHERE destination = '19';

-- STEP 2: Update users (move admin to canonical destination)
UPDATE users SET destination_id = 5 WHERE destination_id = 19;

-- STEP 3: Update keycards (if any)
UPDATE keycards SET destination_id = 5 WHERE destination_id = 19;

-- STEP 4: Delete the duplicate destination
-- ⚠️ ONLY RUN AFTER VERIFYING STEPS 1-3 SUCCEEDED
DELETE FROM destinations WHERE id = 19;

-- STEP 5: Optionally rename to cleaner name
UPDATE destinations SET name = 'Directorate' WHERE id = 5;
```

### Post-Merge Verification

```sql
-- Verify no orphaned references
SELECT 'visitors' AS tbl, COUNT(*) AS cnt FROM visitors WHERE destination = '19'
UNION ALL
SELECT 'users', COUNT(*) FROM users WHERE destination_id = 19
UNION ALL
SELECT 'keycards', COUNT(*) FROM keycards WHERE destination_id = 19;
-- Expected: All counts = 0
```

---

## 5. Data Integrity Issue: visitors.destination

The `visitors` table stores destination as a VARCHAR string:
```sql
`destination` varchar(100) DEFAULT NULL
```

This means:
- No foreign key constraint
- References can become orphaned if destinations are deleted
- Cannot easily JOIN with destinations table

### Recommended Fix (Future Migration)

```sql
-- Create a proper destination_id column
ALTER TABLE visitors ADD COLUMN destination_id INT DEFAULT NULL AFTER destination;

-- Migrate existing data
UPDATE visitors SET destination_id = CAST(destination AS UNSIGNED) WHERE destination REGEXP '^[0-9]+$';

-- Add foreign key
ALTER TABLE visitors 
ADD CONSTRAINT fk_visitor_destination 
FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL;

-- Eventually: drop the old varchar column
-- ALTER TABLE visitors DROP COLUMN destination;
```

---

## 6. Summary

| Category | Count |
|----------|-------|
| Total Destinations | 6 |
| Exact Duplicates | 0 |
| Near-Duplicates | 1 group (IDs 5, 19) |
| Orphan Risk | `visitors.destination` has no FK |

### Action Items

1. ✅ Review the merge plan for "Directorate" duplicates
2. ⚠️ Execute merge SQL after user confirmation
3. 📋 Consider adding proper FK to visitors table (future migration)
