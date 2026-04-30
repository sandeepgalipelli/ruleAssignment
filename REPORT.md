# Rule Assignment Solution Report

## Overview
This solution implements a rule assignment system using object-oriented PHP (8+) and MySQL.  
It supports:
- Group creation
- Hierarchical rule assignment (max 3 tiers)
- Group view in tree order
- Assignment editing with validation and persistence

## Database Design
The relational schema is in `sql/schema.sql` with three core entities:
- `rules`: Stores reusable predefined rules (`condition` / `decision`)
- `groups`: Stores group definitions
- `assignments`: Stores the hierarchical link between group and rules

`assignments` includes:
- `group_id` (group owner)
- `rule_id` (assigned rule)
- `parent_assignment_id` (self reference to model hierarchy)
- `tier` (resolved level 1..3)
- `sort_order` (preserves assignment order within parent)

## Constraint Handling
Validation is implemented in `Services/AssignmentService.php`:
- Max tier = 3
- No cyclic parent-child chains
- Decision rule cannot have children
- Condition rule must have at least one child
- Same rule cannot repeat under the same parent (including root)

Rules may be reused under different parent nodes and in different tiers.

## Application Structure
- `config/database.php`: PDO connection singleton
- `Models/*`: Data model classes (`Rule`, `Group`, `Assignment`)
- `Repositories/*`: Data access classes
- `Services/AssignmentService.php`: Validation + save workflow
- `Utils/TreeBuilder.php`: Builds nested structure for rendering
- `index.php`: Front controller + UI (create/view/edit)

## UI Behavior
- Create page allows adding assignment rows dynamically
- Each row selects:
  - Rule
  - Parent row (or ROOT)
- View page renders hierarchy by tier in tree format
- Edit page loads existing rows and saves updated hierarchy

## Notes
- Rules are seeded in `sql/schema.sql` for quick testing.
- Server-side validation enforces all core constraints.
- Client-side JS focuses on usability; data integrity is enforced in PHP service layer.

