# DaTeam Application - Developer Documentation

## Overview
This document provides comprehensive technical documentation for developers working on the DaTeam car rental platform. It covers architecture, database schema, validation rules, and important implementation details.

---

## 1. Authentication System

### Dual-Guard Architecture
- **Web Guard**: Customer authentication
- **Staff Guard**: Staff member authentication
- Located in `config/auth.php`
- Uses separate database tables: `customers` and `staff`

### Key Controllers
- **AuthController** (`app/Http/Controllers/AuthController.php`)
  - Login (dual-guard support)
  - Registration (customers only)
  - Password reset
  - Logout (both guards)

### Authentication Flow
```
Customer Registration
├─ Validate email, password, phone
├─ Create customer with 'unverified' status
├─ Auto-login to web guard
└─ Redirect to profile completion (mandatory)

Staff Login
├─ Validate credentials
├─ Use 'staff' guard
└─ Redirect to staff dashboard

Password Reset
├─ Send reset link via email
├─ Verify token validity
├─ Update password and auto-login
└─ Redirect to dashboard
```

---

## 2. Database Schema Optimization

### Column Size Constraints (Optimized for Performance)
All string columns have been optimized from 255 characters to appropriate sizes:

**Personal Information:**
- `fullName`: 100 characters
- `email`: 100 characters
- `phoneNo`: 20 characters
- `emergency_contact_no`: 20 characters
- `emergency_contact_name`: 100 characters

**Identity & Verification:**
- `stustaffID`: 50 characters
- `ic_passport`: 50 characters
- `driving_license_expiry`: 50 characters
- `faculty`: 100 characters
- `nationality`: 50 characters

**Address Information:**
- `homeAddress`: text field (unlimited)
- `collegeAddress`: text field (unlimited)

**Banking:**
- `bankName`: 100 characters
- `bankAccountNo`: 50 characters

**Vehicle Information:**
- `plateNo`: 20 characters
- `model`: 100 characters
- `brand`: 100 characters
- `type`: 50 characters
- `fuelType`: 50 characters
- `color`: 50 characters

**Status Fields:**
- `accountStat`: 50 characters
- `bookingStatus`: 50 characters
- `paymentStatus`: 50 characters
- `penaltyStatus`: 50 characters
- `tier` (loyalty): 50 characters

**File Paths:**
- `avatar`, `student_card_image`, etc.: 255 characters (full paths)

---

## 3. Password Policy

### Requirements
- **Minimum Length**: 8 characters
- **Maximum Length**: 8 characters (recent change)
- **Confirmation Required**: On registration, password reset, and updates
- **Hashing Method**: bcrypt (Laravel default via `Hash::make()`)
- **Verification**: `Hash::check()` for password comparisons

### Important Notes
⚠️ **Critical**: Always use `Hash::make()` to hash passwords before storage.
⚠️ **Never** store plain text passwords.
✅ **Always** use `Hash::check()` when comparing passwords.

### Password Update Validation Rules
```php
// Customer password update
'password' => ['required', 'string', 'min:8', 'confirmed']

// Staff password on creation
'password' => 'required|min:8|confirmed'

// Staff password on update (optional)
'password' => 'nullable|min:8|confirmed'
```

---

## 4. File Upload System

### Avatar Upload (Dual Storage)
**Location**: `ProfileController::updateAvatar()`

**Storage Strategy:**
1. **Local Storage** (Primary)
   - Path: `public/storage/profilepic/`
   - Format: `profile_[userID]_[timestamp].[ext]`
   - Auto-cleanup: Old avatar deleted
   
2. **Google Drive** (Backup)
   - Location: Customer's personal folder
   - Non-blocking (async)
   - Handles errors gracefully

**Process:**
- Validate image (max 5MB)
- Generate unique filename
- Create storage directory if missing
- Move file to public folder
- Optionally backup to Google Drive
- Reset account status if previously rejected

### Document Upload (Profile)
**Location**: `ProfileController::update()`

**Documents:**
- Student Card Image
- IC/Passport Image
- Driving License Image

**Upload Destination:**
- Google Drive folder: `[StudentID] - [FullName]`
- File naming: `[Date] - [DocumentType].[ext]`
- Max size: 5MB per file

---

## 5. Validation Rules

### Column Size References in Validation

All validation `max:X` rules correspond to database column sizes:

```php
// Customer Profile Validation
$request->validate([
    'name' => ['required', 'string', 'max:100'],                    // fullName column
    'email' => ['required', 'email', 'max:100'],                    // email column
    'phone' => ['required', 'regex:/^[0-9\-\+\s]+$/', 'max:20'],   // phoneNo column
    'emergency_contact_no' => ['required', 'max:20'],               // emergency_contact_no column
    'emergency_contact_name' => ['required', 'string', 'max:100'],  // custom field
    'student_staff_id' => ['required', 'string', 'max:50'],         // stustaffID column
    'ic_passport' => ['required', 'string', 'max:50'],              // ic_passport column
    'driving_license_expiry' => ['required', 'date'],               // driving_license_expiry column
    'nationality' => ['required', 'string', 'max:50'],              // nationality column
    'faculty' => ['required', 'string', 'max:100'],                 // faculty column
    'bank_name' => ['required', 'string', 'max:100'],               // bankName column
    'bank_account_no' => ['required', 'string', 'max:50'],          // bankAccountNo column
]);
```

### Custom Validation Messages
```php
'name.regex' => 'Full name can only contain letters and spaces.'
'phone.regex' => 'Phone number can only contain numbers, hyphens, and plus signs.'
```

---

## 6. Controller Documentation

### ProfileController
**File**: `app/Http/Controllers/ProfileController.php`

**Methods:**
- `edit()` - Display profile form
- `updateAvatar()` - Handle avatar upload (local + Drive backup)
- `update()` - Update all profile information with document uploads
- `updatePassword()` - Change password with current password verification

**Key Features:**
- Dual-storage avatar system
- Google Drive document backup
- Status reset on profile update
- Account verification status management

### AuthController
**File**: `app/Http/Controllers/AuthController.php`

**Methods:**
- `showLogin()` - Customer login form
- `showStaffLogin()` - Staff login form
- `login()` - Authenticate user with guard selection
- `showRegister()` - Customer registration form
- `register()` - Create customer account
- `showLinkRequestForm()` - Password reset request form
- `sendResetLinkEmail()` - Send reset link
- `showResetForm()` - Display reset form
- `reset()` - Process password reset
- `logout()` - Logout from both guards

**Key Features:**
- Dual-guard support (web + staff)
- Auto-login after registration
- Token-based password reset
- Session security (token regeneration)

### StaffManagementController
**File**: `app/Http/Controllers/StaffManagementController.php`

**Methods:**
- `index()` - List all staff (paginated)
- `create()` - Show creation form
- `store()` - Create new staff member
- `edit()` - Show edit form
- `update()` - Update staff details
- `destroy()` - Delete staff (with self-deletion prevention)

**Key Features:**
- Self-deletion prevention (security)
- Role-based creation (admin/staff)
- Paginated listing (10 per page)
- Password optional on update

### StaffProfileController
**File**: `app/Http/Controllers/StaffProfileController.php`

**Methods:**
- `edit()` - Display staff profile form
- `update()` - Update staff information

**Key Features:**
- Staff guard authentication
- Optional password updates
- Email uniqueness validation

---

## 7. Database Migrations

### Main Migration
**File**: `database/migrations/2025_12_19_000000_create_hasta_erd_tables.php`

Includes:
- System tables (sessions, cache, jobs)
- Core tables (staff, customers, vehicles)
- Operational tables (bookings, payments, penalties)
- Service tables (loyalties, inspections, maintenance)

### Additional Migrations
- `2025_12_23_093333_add_profile_fields_to_customers_table.php`
- `2025_12_27_162741_update_vehicles_table_for_hasta.php`
- `2025_12_30_034206_add_rejection_reason_to_customers.php`

---

## 8. Important Development Notes

### Security Considerations
1. **Always hash passwords**: Use `Hash::make()` before storing
2. **Verify passwords securely**: Use `Hash::check()` for comparison
3. **Regenerate CSRF tokens**: On logout for session security
4. **Validate file uploads**: Check type, size, MIME type
5. **Escape user input**: Especially for Google Drive API queries

### Database Optimization
- Column sizes optimized to actual data needs
- Reduces storage overhead
- Improves query performance
- All validations enforce these limits

### API Integration
- **Google Drive**: Used for document backup
- **Environment Variables**: Configure in `.env`
  - `GOOGLE_DRIVE_CLIENT_ID`
  - `GOOGLE_DRIVE_CLIENT_SECRET`
  - `GOOGLE_DRIVE_REFRESH_TOKEN`
  - `GOOGLE_DRIVE_CUSTOMER_INFORMATION`

### Error Handling
- Graceful degradation: Drive errors don't stop local saves
- Detailed exception messages for debugging
- Logging for critical operations
- User-friendly error messages in redirects

---

## 9. Common Workflows

### Customer Registration Flow
```
1. Display registration form
2. Submit email, password, phone
3. Create customer account (status: unverified)
4. Auto-login
5. Redirect to profile completion
6. Complete profile with all required fields
7. Status changes to 'pending' (awaiting staff approval)
8. Staff approves → status: 'Confirmed'
9. Customer can now make bookings
```

### Staff Account Management
```
1. Admin creates new staff account
2. Set name, email, phone, role
3. System generates password
4. Staff receives credentials
5. Staff updates profile (optional password change)
6. Staff can manage customers/bookings
```

### Password Reset Flow
```
1. User clicks "Forgot Password"
2. Enters email
3. System sends reset link via email
4. User clicks link (contains token)
5. User enters new password (confirmed)
6. System validates token
7. Updates password and auto-logs in
8. Redirects to dashboard
```

---

## 10. Testing Considerations

### Unit Testing
- Test password hashing/verification
- Test validation rules
- Test file upload handling

### Integration Testing
- Test authentication flow (both guards)
- Test password reset process
- Test profile updates
- Test Google Drive integration

### Manual Testing
- Create accounts as customer and staff
- Test profile updates
- Test avatar upload
- Test password reset
- Test logout from both guards

---

## 11. Deployment Checklist

- [ ] Verify `.env` has all required variables
- [ ] Test Google Drive API credentials
- [ ] Check file storage permissions (755)
- [ ] Run database migrations
- [ ] Verify SSL certificate (HTTPS)
- [ ] Test email service (password reset)
- [ ] Test password hashing with production data
- [ ] Verify session configuration
- [ ] Test CSRF token regeneration

---

## 12. Quick Reference

### Important Constants
- Password min: 8 characters
- Password max: 8 characters
- Email max: 100 characters
- Name max: 100 characters
- Phone max: 20 characters
- Avatar max: 5MB
- Document max: 5MB per file

### Key Routes (Auth)
- `/login` - Customer login
- `/staff/login` - Staff login
- `/register` - Customer registration
- `/forgot-password` - Password reset request
- `/reset-password/{token}` - Password reset form
- `/logout` - Logout

### Key Routes (Profile)
- `/profile/edit` - Edit profile
- `/profile/update` - Update profile
- `/profile/avatar` - Upload avatar
- `/profile/password` - Update password

---

## 13. Support & Contact

For questions or issues, contact the development team.

**Last Updated**: January 31, 2026
**Version**: 1.0
**Status**: Production Ready
