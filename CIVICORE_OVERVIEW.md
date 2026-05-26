# CiviCore: Comprehensive Project Overview

## 📋 Table of Contents
1. [What is CiviCore?](#what-is-civicore)
2. [Core Principles](#core-principles)
3. [User Roles & Permissions](#user-roles--permissions)
4. [Architecture Overview](#architecture-overview)
5. [Feature Modules](#feature-modules)
6. [Database Architecture](#database-architecture)
7. [Authentication & Authorization](#authentication--authorization)
8. [Payment System (CiviPay)](#payment-system-civipay)
9. [Data Models](#data-models)
10. [API Structure](#api-structure)
11. [Frontend Architecture](#frontend-architecture)
12. [Admin Interface](#admin-interface)
13. [Technical Stack](#technical-stack)

---

## What is CiviCore?

**CiviCore** is an internal residential community management system designed for housing administrators and residents. It is **NOT a public SaaS platform** but rather a private management tool for small to medium-sized residential complexes (blocks of housing units).

### Primary Purpose:
- **Transparent Financial Management** — Clear record of who paid what and when
- **Approval Workflow** — Multi-step verification of payments before acceptance
- **Role-Based Access Control** — Different users see and do different things
- **Sustainability** — Long-term maintainability with modular architecture
- **User-Friendly UX** — Designed for non-technical administrators and residents

### Who Uses It:
- **Administrators** — Full system control
- **Treasurers** — Payment review and approval
- **Block Coordinators** — Input payments for their assigned block
- **Residents** — View their payment history and status

---

## Core Principles

| Principle | Description |
|-----------|-------------|
| **Data Integrity** | Historical payment records must never be retroactively altered |
| **Transparency** | All actions are audited and traceable |
| **Security** | Role-based permissions enforce strict data access |
| **Modularity** | Designed to support future modules beyond payments |
| **Simplicity** | Minimal complexity in UX for non-technical users |
| **Scalability** | Can manage multiple blocks and hundreds of residents |

---

## User Roles & Permissions

CiviCore implements a 4-tier role-based access control system:

### 1. **ADMIN** (System Administrator)
**Full system control. Also acts as Treasurer.**

**Capabilities:**
- ✅ Full access to all modules
- ✅ Create, edit, deactivate user accounts
- ✅ Manage blocks and units
- ✅ Approve or reject payments
- ✅ Edit approved payments directly (no re-approval needed)
- ✅ Generate financial reports
- ✅ View complete audit history
- ✅ Manage system settings and configuration
- ✅ Access CMS for public homepage

**Cannot:**
- ❌ Only one Admin per system

---

### 2. **TREASURER** (Payment Reviewer)
**Reviews and approves pending payment submissions.**

**Capabilities:**
- ✅ View all pending payments
- ✅ Approve payments (moves to "Approved" status)
- ✅ Reject payments with detailed rejection reason
- ✅ View payment proof uploads (transfer receipts, cash photos)
- ✅ Input payments directly (auto-approved as Treasurer input)
- ✅ View payment history
- ✅ Generate reports

**Cannot:**
- ❌ Cannot edit approved payments
- ❌ Cannot manage user accounts
- ❌ Cannot manage blocks/units
- ❌ Cannot delete payments
- ❌ Cannot access system settings

---

### 3. **BLOCK COORDINATOR** (Local Coordinator)
**Manages payments for one assigned block.**

**Capabilities:**
- ✅ Input payments for residents in their block
- ✅ Upload payment proof (transfer receipt or cash photo)
- ✅ View payment history for their block only
- ✅ Edit their own submissions (but requires re-approval)
- ✅ Monitor unpaid residents in their block
- ✅ View their monthly performance dashboard

**Cannot:**
- ❌ Cannot approve or reject payments
- ❌ Cannot manually approve their own edits
- ❌ Cannot view other blocks' data
- ❌ Cannot manage users or blocks
- ❌ Cannot generate reports

**Special Constraint:**
- Each Block Coordinator is assigned to **exactly ONE block**
- Receives notifications for pending payments in their block

---

### 4. **RESIDENT** (Community Member)
**Read-only personal account for viewing payment status.**

**Capabilities:**
- ✅ View personal payment history
- ✅ See monthly payment status (paid, pending, unpaid)
- ✅ View current year and previous year data only
- ✅ View household family members
- ✅ View personal profile information

**Cannot:**
- ❌ Cannot submit payments
- ❌ Cannot edit any data
- ❌ Cannot view other residents' data
- ❌ Cannot access financial records
- ❌ Cannot view data older than 2 years

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                  CiviCore Application                        │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │           Frontend Layer (React SPA)                 │   │
│  ├──────────────────────────────────────────────────────┤   │
│  │  • Dashboard              • User Management          │   │
│  │  • Payments               • Reports                  │   │
│  │  • Residents              • Settings                 │   │
│  │  • Blocks & Units         • Homepage CMS             │   │
│  └──────────────────────────────────────────────────────┘   │
│                          ↓ HTTP + JSON                      │
│  ┌──────────────────────────────────────────────────────┐   │
│  │           Backend API (Laravel)                      │   │
│  ├──────────────────────────────────────────────────────┤   │
│  │  Controllers:                                        │   │
│  │  • PaymentController      • ResidentController       │   │
│  │  • BlockController        • UserController           │   │
│  │  • ReportController       • RoleController           │   │
│  │  • HomepageController     • SettingController        │   │
│  │                                                      │   │
│  │  Middleware:                                         │   │
│  │  • Authentication         • Permission checks       │   │
│  │  • API Key validation     • Session management       │   │
│  └──────────────────────────────────────────────────────┘   │
│                          ↓                                   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │       Business Logic Layer (Eloquent Models)         │   │
│  ├──────────────────────────────────────────────────────┤   │
│  │  • User.php           • Block.php                    │   │
│  │  • Resident.php       • Unit.php                     │   │
│  │  • PaymentRecord.php  • Role.php                     │   │
│  │  • FeeHistory.php     • Setting.php                  │   │
│  └──────────────────────────────────────────────────────┘   │
│                          ↓                                   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │        Data Persistence Layer (MySQL Database)       │   │
│  ├──────────────────────────────────────────────────────┤   │
│  │  Tables:                                             │   │
│  │  • users              • residents                    │   │
│  │  • blocks             • units                        │   │
│  │  • roles              • permissions                  │   │
│  │  • payment_records    • payment_methods              │   │
│  │  • family_members     • fee_history                  │   │
│  │  • media_files        • settings                     │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │       File Storage Layer (Private Storage)           │   │
│  ├──────────────────────────────────────────────────────┤   │
│  │  • Payment proof uploads (transfer receipts)         │   │
│  │  • Resident photos                                   │   │
│  │  • Generated reports (Excel/PDF)                     │   │
│  │  • Media files                                       │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## Feature Modules

### 1. **Dashboard** 📊
**Home page providing quick overview of the system status.**

**Features:**
- Quick statistics (total residents, unpaid accounts, monthly summary)
- Recent transactions
- Role-specific summaries
- Quick action shortcuts

**Visible to:** Admin, Treasurer, Block Coordinator, Resident

---

### 2. **Payment Management (CiviPay)** 💰
**Core module for managing resident payments and fees.**

**Components:**
- Payment submission and tracking
- Multi-stage approval workflow
- Proof of payment uploads
- Payment history and reporting
- Fee management

**See detailed section:** [Payment System (CiviPay)](#payment-system-civipay)

---

### 3. **Resident Management** 👥
**Complete CRUD operations for resident profiles.**

**Features:**
- Create new residents and link to blocks/units
- Edit resident information (name, phone, email, notes)
- Upload resident photos
- View payment history per resident
- Deactivate/reactivate residents
- Family member management (see next section)

**Fields Tracked:**
- Full name
- Phone number
- Email address
- Block and Unit assignment
- Family card number (encrypted)
- House status (owner-occupied, rented, etc.)
- Resident photo
- Personal notes
- Activation status

---

### 4. **Family Member Management** 👨‍👩‍👧‍👦
**Manage household composition and family relationships.**

**Features:**
- Add multiple family members per resident
- Mark head-of-family
- Track family relationships
- View household composition

**Use Cases:**
- Know who lives in each unit
- Identify primary contact person (head of family)
- Track rental arrangements

---

### 5. **Block & Unit Management** 🏢
**Manage residential blocks and individual units.**

**Features:**

**Blocks:**
- Create/edit/delete blocks
- Assign block coordinators
- View all units in a block
- Monitor block-level statistics

**Units:**
- Create units within blocks
- Assign residents to units
- Track unit status (vacant, occupied, rented)
- Manage unit capacity

**Data Structure:**
```
Block
├── Block Name
├── Block Description
├── Total Units
├── Assigned Coordinator
└── Units
    ├── Unit Number (e.g., "A-101", "B-205")
    ├── Unit Status (vacant, owner-occupied, rented)
    ├── Assigned Resident
    └── Unit Details
```

---

### 6. **User Account Management** 🔐
**Create and manage user accounts for all roles.**

**Features:**
- Create user accounts manually
- Manage user activation/deactivation
- Assign roles (Admin, Treasurer, Coordinator, Resident)
- Assign blocks to coordinators
- Reset passwords
- View user activity

**Registration Flows:**
1. **Self-Registration** — User creates account (inactive until approved)
2. **Google Authentication** — User registers via Gmail
3. **Admin Creation** — Admin manually creates account (active by default)

**Rules:**
- Default user becomes inactive after self-registration
- Admin must manually activate new self-registered users
- Maximum 3 user accounts per residential unit (configurable)
- Email must be unique across system

---

### 7. **Role & Permission Management** 🔑
**Define and manage role-based access control.**

**Features:**
- View all system roles
- Manage role permissions
- Assign roles to users
- Permission hierarchy enforcement
- Activity audit trail

**Permission Types:**
- `residents.view` / `residents.create` / `residents.edit` / `residents.delete`
- `blocks.view` / `blocks.create` / `blocks.edit` / `blocks.delete`
- `payments.view` / `payments.create` / `payments.edit` / `payments.delete` / `payments.approve`
- `users.view` / `users.create` / `users.edit` / `users.delete`
- `reports.view` / `reports.create`
- `homepage.view` / `homepage.edit` / `homepage.create` / `homepage.delete`

---

### 8. **Financial Reporting** 📈
**Generate financial reports and export data.**

**Report Features:**
- **Payment Status Report** — shows which residents paid, when, and how much
- **Block-Level Reports** — aggregate payment data per block
- **Year-wise Filtering** — view any fiscal year
- **Column Format** — months as columns, residents as rows
- **Export to Excel** — for offline analysis and archival

**Data Included:**
- Resident name
- Unit number
- Payment date
- Amount paid
- Payment method
- Current balance
- Unpaid months

**Formula Preservation:**
- Historical fee values are preserved
- Accurate historical reporting even if fee amounts change

---

### 9. **Homepage Management (CMS)** 🌐
**Manage public-facing website content.**

**Features:**
- **Hero Section** — Edit main banner and tagline
- **About Section** — Content about the residential community
- **Events** — Create, edit, delete community events
- **Memorable Moments** — Gallery of community highlights
- **Featured Event** — Pin an event as featured

**Content Types:**
- Text content (title, description)
- Images (hero, event photos)
- Event dates and times
- External links

---

### 10. **Public Homepage** 🏠
**Read-only public-facing website for community information.**

**Visible Content:**
- About the community
- Upcoming events
- Community photos/gallery
- General information
- **No payment information visible to public**

---

### 11. **Resident Overview Page** 📄
**Personalized dashboard for individual residents.**

**Features:**
- View personal payment history
- Current month payment status
- Outstanding balance
- Family member information
- Personal profile

**Access:**
- Residents can only view their own data
- Limited to current year and previous year

---

### 12. **Settings Management** ⚙️
**System-wide configuration.**

**Settings:**
- Pagination defaults (20 per page for payments, 15 for residents, etc.)
- Maximum accounts per unit (default: 3)
- Internal API key configuration
- Email settings
- File upload limits
- Database backup settings

---

### 13. **Media Management** 📷
**Handle all file uploads and storage.**

**Supported Files:**
- Resident photos
- Payment proof (receipts, cash photos)
- Generated reports (Excel, PDF)
- Homepage images
- Event photos

**Security:**
- Private file serving (auth-required)
- Files not directly accessible via URL
- Unique file naming/hashing
- Storage path access control

---

### 14. **Audit & Session Management** 🔍
**Track user activity and manage active sessions.**

**Logged Actions:**
- User login/logout
- Payment approvals/rejections
- Data modifications (residents, payments, users)
- Report generation
- File uploads

**Conflict Resolution:**
- Single-session enforcement (user can only be logged in once)
- Session conflict detection
- Automatic session termination on new login

---

## Database Architecture

### Core Tables

#### **users**
```
id (UUID)
name (string)
username (string, unique)
email (string, unique)
password (hashed)
is_active (boolean)
google_id (string, nullable)
role_id (UUID, foreign key)
block_id (UUID, nullable, foreign key) ← for block coordinators
unit_number (string, nullable)
avatar (string, nullable)
language (string, default: 'en')
session_token (string, nullable)
last_login_at (timestamp)
last_active_at (timestamp)
created_at
updated_at
```

**Purpose:** Store user account information and authentication data.

---

#### **residents**
```
id (UUID)
user_id (UUID, nullable, foreign key) ← link to user account
block_id (UUID, foreign key)
unit_id (UUID, foreign key)
fullname (string)
phone (string)
email (string, nullable)
is_active (boolean)
family_card_number (encrypted string, nullable)
notes (text, nullable)
photo_path (string, nullable)
created_at
updated_at
```

**Purpose:** Store resident profiles and family information.

---

#### **blocks**
```
id (UUID)
name (string)
description (text, nullable)
created_at
updated_at
```

**Purpose:** Represent residential blocks/sections.

---

#### **units**
```
id (UUID)
block_id (UUID, foreign key)
unit_number (string, unique within block)
house_status (enum: owner_occupied, rented, vacant)
created_at
updated_at
```

**Purpose:** Represent individual housing units within blocks.

---

#### **payment_records**
```
id (UUID)
resident_id (UUID, foreign key)
batch_id (UUID, nullable, foreign key)
payment_month (date) ← which month's fee this covers
amount (decimal:2)
payment_method_id (UUID, foreign key)
proof_path (string, nullable) ← file path to receipt/photo
status (enum: unpaid, pending, approved, rejected)
rejection_reason (text, nullable)
notes (text, nullable)
submitted_by (UUID, foreign key) ← user who submitted
approved_by (UUID, nullable, foreign key) ← user who approved
approved_at (timestamp, nullable)
created_at
updated_at
```

**Purpose:** Store all payment records with full audit trail.

---

#### **payment_methods**
```
id (UUID)
name (string) ← e.g., "Bank Transfer", "Cash"
description (text, nullable)
is_active (boolean)
created_at
updated_at
```

**Purpose:** Define valid payment methods (Bank Transfer, Cash, Check, etc.).

---

#### **fee_history**
```
id (UUID)
resident_id (UUID, foreign key)
amount (decimal:2)
effective_from (date) ← when this fee amount starts
created_at
updated_at
```

**Purpose:** Track historical fee amounts per resident to enable accurate historical reporting.

---

#### **family_members**
```
id (UUID)
resident_id (UUID, foreign key)
fullname (string)
relationship (string, nullable)
is_head (boolean, default: false)
created_at
updated_at
```

**Purpose:** Store household composition and family relationships.

---

#### **roles**
```
id (UUID)
name (string) ← e.g., "admin", "treasurer", "block_coordinator", "resident"
description (text, nullable)
created_at
updated_at
```

**Purpose:** Define available user roles.

---

#### **permissions**
```
id (UUID)
role_id (UUID, foreign key)
permission (string) ← e.g., "payments.approve"
created_at
updated_at
```

**Purpose:** Store fine-grained permissions per role.

---

#### **settings**
```
id (UUID)
key (string, unique)
value (text, nullable)
created_at
updated_at
```

**Purpose:** Store configurable system settings (pagination, API keys, etc.).

---

#### **media_files**
```
id (UUID)
user_id (UUID, foreign key)
model_type (string) ← polymorphic reference
model_id (UUID)
file_path (string)
file_name (string)
file_size (integer)
mime_type (string)
created_at
updated_at
```

**Purpose:** Track all uploaded files and their relationships.

---

## Authentication & Authorization

### Authentication Flow

```
1. User visits /login
   ↓
2. Choose auth method:
   a) Email + Password
   b) Google OAuth
   ↓
3. Credentials validated
   ↓
4. Check if user is_active
   ↓
5. If Single Session Enabled:
   - Check if user logged in elsewhere
   - If yes: Show conflict page
   - If no: Create session
   ↓
6. Set session token
   ↓
7. Record last_login_at
   ↓
8. Redirect to /dashboard
```

### Authorization via Permissions

**Check during every protected action:**
```php
Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve'])
    ->middleware('permission:payments.approve');
```

**Permission Types:**
- Resource-based: `payments.view`, `residents.create`, etc.
- Action-based: `payments.approve`, `users.delete`, etc.
- Hierarchical: Admin > Treasurer > Coordinator > Resident

---

## Payment System (CiviPay)

### Payment Model

**Each Resident has:**
- Multiple payments (one per fee payment cycle)
- Exactly ONE active monthly fee amount
- Fee change effective date (applies only from month X onwards)
- Payment history (never retroactively modified)

### Payment Statuses

| Status | Description | Who Can Change | Result |
|--------|-------------|----------------|--------|
| **unpaid** | Payment not yet submitted | N/A | Month shows as unpaid |
| **pending** | Submitted for approval | Treasurer/Admin | Awaiting review |
| **approved** | Verified and accepted | Treasurer/Admin | Month marked paid |
| **rejected** | Declined with reason | Treasurer/Admin | Coordinator must resubmit |

### Two Payment Input Flows

#### Flow A: Coordinator Input (Most Common)
```
1. Resident pays coordinator in cash or bank transfer
   ↓
2. Coordinator inputs payment details
   - Payment amount
   - Payment month covered
   - Payment method
   ↓
3. Coordinator uploads proof
   - Receipt (for transfer)
   - Photo (for cash)
   ↓
4. Status = "Pending Approval"
   ↓
5. Treasurer/Admin reviews proof
   ↓
6. Decision:
   ✓ Approve → Status = "Approved" → Month marked paid
   ✗ Reject → Status = "Rejected" → Reason stored
   ↓
7. If Rejected:
   - Coordinator notified
   - Can edit and resubmit (requires re-approval)
```

#### Flow B: Treasurer Direct Input (Bypass)
```
1. Treasurer receives payment directly
   (e.g., bank deposit, check in mail)
   ↓
2. Treasurer inputs payment details
   ↓
3. Upload proof (if available)
   ↓
4. Status = "Approved" (auto-approved as treasurer)
   ↓
5. Month marked paid immediately
```

### Key Rules

- **One payment can cover multiple months** (e.g., resident pays 3 months in advance)
- **All payments require proof** (receipt photo or transfer record)
- **Admin can edit approved payments** (no re-approval needed)
- **Coordinator edits require re-approval**
- **Payment history is immutable** (cannot delete or retroactively modify historical records)
- **Fee changes preserved** (reports show correct fee for each historical month)

### Payment Approval Workflow

```
Coordinator Submits Payment
    ↓
Status = "Pending"
    ↓
Treasurer Reviews
    ├→ (Approve)
    │    ↓
    │    Status = "Approved"
    │    ↓
    │    Resident Notified
    │    ↓
    │    Month Marked Paid
    │
    └→ (Reject)
         ↓
         Status = "Rejected"
         ↓
         Rejection Reason Stored
         ↓
         Coordinator Notified
         ↓
         Coordinator Can Re-Edit & Resubmit
```

---

## Data Models

### Core Models & Relationships

```
User (1 — Account)
├── role_id → Role (many-to-one)
├── block_id → Block (for coordinators only)
└── resident() → Resident (one-to-one, optional)

Resident (1 — Person living in unit)
├── user_id → User (optional, has account)
├── block_id → Block (required)
├── unit_id → Unit (required)
├── familyMembers() → FamilyMember (one-to-many)
└── payments() → PaymentRecord (one-to-many)

Block (1 — Section of housing)
├── units() → Unit (one-to-many)
├── residents() → Resident (one-to-many)
└── coordinator() → User (for block coordinator)

Unit (1 — Individual home)
├── block_id → Block (required)
└── resident() → Resident (optional)

PaymentRecord (1 — Payment transaction)
├── resident_id → Resident (required)
├── payment_method_id → PaymentMethod
├── submitted_by → User (who input it)
├── approved_by → User (who approved it, nullable)
└── batch_id → PaymentBatch (nullable)

FeeHistory (1 — Historical fee record)
├── resident_id → Resident
└── effective_from (date) ← when this fee starts

FamilyMember (1 — Household member)
├── resident_id → Resident (required)
└── is_head (boolean) ← marks head of household

Role (1 — User role type)
└── permissions() → Permission (one-to-many)

Permission (many — Role permission)
├── role_id → Role
└── permission (string, e.g., "payments.approve")
```

---

## API Structure

### Base URL
```
http://localhost/api/
```

### Authentication
- **Session-based** (cookies) for web UI
- **X-Api-Key header** for internal React SPA calls
- **OAuth tokens** for external integrations (future)

### Key Endpoints

#### Residents
```
GET    /residents                    - List all residents
POST   /residents                    - Create resident
PUT    /residents/{id}               - Update resident
DELETE /residents/{id}               - Delete resident
PATCH  /residents/{id}/deactivate    - Deactivate resident
```

#### Payments
```
GET    /payments                     - List payments
POST   /payments                     - Create payment
PUT    /payments/{id}                - Update payment
PATCH  /payments/{id}/approve        - Approve payment
PATCH  /payments/{id}/reject         - Reject payment
GET    /payments/{id}/proof          - Download proof file
```

#### Blocks
```
GET    /blocks                       - List blocks
POST   /blocks                       - Create block
PUT    /blocks/{id}                  - Update block
DELETE /blocks/{id}                  - Delete block
GET    /blocks/{id}/units            - List units in block
POST   /blocks/{id}/units            - Create unit
```

#### Reports
```
GET    /reports                      - List available reports
POST   /reports/generate             - Generate custom report
GET    /reports/{id}/download        - Download report file
GET    /reports/payment-status       - Payment status by month
```

#### Homepage Content
```
GET    /api/homepage                 - Get homepage data (public API)
POST   /homepage/hero                - Update hero section
POST   /homepage/about               - Update about section
POST   /homepage/events              - Create event
PUT    /homepage/events/{id}         - Update event
DELETE /homepage/events/{id}         - Delete event
```

---

## Frontend Architecture

### Technology Stack
- **Framework:** React 18+ (SPA)
- **Build Tool:** Vite
- **Styling:** CSS modules or Tailwind CSS
- **State Management:** TBD

### Key Pages/Routes

#### Public Routes
```
/                    - Public homepage
/login               - Login page
/register            - Registration form
/forgot-password     - Password reset request
/reset-password/:token - Password reset form
/session-conflict    - Session conflict handler
```

#### Protected Routes (Auth Required)
```
/dashboard           - Main dashboard
/residents           - Resident directory
/residents/create    - Create resident form
/residents/:id/edit  - Edit resident form
/blocks              - Block management
/units               - Unit management
/payments            - Payment listing
/payments/create     - Create payment form
/reports             - Report generation
/users               - User management
/roles               - Role management
/settings            - System settings
/homepage            - Homepage CMS
/overview            - Resident personal page (residents only)
```

### Component Structure
```
src/
├── components/
│   ├── Auth/          (Login, Register, etc.)
│   ├── Dashboard/
│   ├── Residents/
│   ├── Payments/
│   ├── Blocks/
│   ├── Reports/
│   ├── Users/
│   ├── Common/        (Header, Sidebar, etc.)
│   └── Forms/
├── hooks/             (Custom React hooks)
├── services/          (API calls)
├── utils/             (Helper functions)
├── pages/             (Page components)
├── styles/            (Global styles)
└── App.jsx
```

---

## Admin Interface

### Navigation Structure
```
CiviCore Admin
├── Dashboard
│   └── Quick Stats, Recent Activity
├── Payments (CiviPay)
│   ├── All Payments → View, Edit, Approve, Reject
│   ├── Pending Review
│   ├── Approved
│   ├── Rejected
│   └── Payment Methods
├── Residents
│   ├── Directory → View, Edit, Deactivate
│   ├── Add New Resident
│   ├── Family Members → Manage per resident
│   └── Bulk Upload (future)
├── Blocks & Units
│   ├── Manage Blocks
│   ├── Manage Units per Block
│   ├── Assign Residents
│   └── Block Overview
├── Users & Access
│   ├── User Management (Create, Edit, Deactivate)
│   ├── Roles & Permissions (Define roles)
│   ├── Active Sessions (View, terminate)
│   └── Audit Log (View all actions)
├── Reports
│   ├── Payment Status Report
│   ├── Block Payment Summary
│   ├── Resident Payment History
│   ├── Generate Custom Report
│   └── Export (Excel, PDF)
├── Homepage (CMS)
│   ├── Hero Section
│   ├── About Section
│   ├── Events (Manage)
│   ├── Gallery/Memorable Moments
│   └── Preview
├── Settings
│   ├── Pagination Settings
│   ├── Registration Limits
│   ├── API Configuration
│   ├── Email Settings
│   ├── File Upload Limits
│   └── System Preferences
└── Help & Support
    ├── Documentation
    ├── FAQ
    └── Support Contact
```

### CRUD States in Admin

Every resource (Residents, Payments, Users, etc.) includes:

1. **List View**
   - Sortable columns
   - Searchable
   - Paginated
   - Filters
   - "Create New" button
   - Empty state handling

2. **Create Form**
   - All required fields
   - Validation messages
   - Save/Cancel buttons
   - Success notification

3. **Edit Form**
   - Pre-filled data
   - Same validation as create
   - Save/Cancel buttons
   - Success notification

4. **Delete/Deactivate**
   - Confirmation dialog
   - Reason field (if applicable)
   - "Are you sure?" warning
   - Cancel/Confirm buttons

---

## Technical Stack

### Backend
- **Framework:** Laravel 11+
- **Language:** PHP 8.2+
- **Database:** MySQL 8.0+
- **Authentication:** Laravel Auth + Google OAuth
- **ORM:** Eloquent
- **Task Queue:** (optional) Laravel Queues
- **File Storage:** Laravel Storage (private disk)
- **Testing:** PHPUnit

### Frontend
- **Framework:** React 18+
- **Build Tool:** Vite
- **Styling:** Tailwind CSS or CSS Modules
- **HTTP Client:** Axios or Fetch API
- **Form Handling:** React Hook Form or Formik
- **Testing:** Vitest or Jest

### DevOps & Deployment
- **Server:** Apache/Nginx
- **PHP Server:** Apache/Nginx + PHP-FPM
- **Database:** MySQL 8.0+ hosted locally or remote
- **File Storage:** Local storage (XAMPP) or S3 (production)
- **Version Control:** Git
- **CI/CD:** GitHub Actions (optional)

### Security
- **Authentication:** Session-based + OAuth
- **Authorization:** Permission-based middleware
- **Data Encryption:** Encrypted fields (family card number)
- **File Security:** Private file serving (no direct URL access)
- **Rate Limiting:** Throttled login attempts
- **CSRF Protection:** Laravel CSRF tokens
- **SQL Injection Prevention:** Eloquent ORM parameterized queries

---

## Summary

**CiviCore** is a sophisticated residential management system purpose-built for transparent payment tracking, multi-role approval workflows, and long-term organizational sustainability. Its modular architecture supports the current Civipay payment module while allowing seamless expansion to additional community management features in the future.

### Key Strengths
✅ Role-based access control (Admin, Treasurer, Coordinator, Resident)  
✅ Multi-stage payment approval workflow  
✅ Immutable payment history for audit compliance  
✅ Historical fee tracking for accurate reporting  
✅ Modular architecture for future expansion  
✅ User-friendly, non-technical UX  
✅ Google OAuth support for easy registration  
✅ Session conflict detection (single login per user)  
✅ Comprehensive audit trail  

### Future-Ready Areas
- Additional modules beyond payments
- Advanced reporting and forecasting
- Mobile app integration
- Multi-language support (infrastructure in place)
- WhatsApp/SMS notifications
- Batch payment processing
- Subscription management

---

**Version:** 1.0  
**Last Updated:** April 2026  
**Status:** Active Development
