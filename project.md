# BeeCore — ISP SaaS Platform
## Product Requirements Document (PRD)

**Product Name:** BeeCore  
**Positioning:** The Operating System for ISPs  
**Target Market:** Bangladesh initially; designed for future international expansion  
**Product Type:** Multi-Tenant SaaS  
**Primary Languages:** Bangla, English  
**Primary Currency:** BDT, with multi-currency architecture  
**Document Status:** Product Blueprint / Development Specification

---

# 1. Executive Summary

BeeCore is a cloud-based, multi-tenant ISP management SaaS platform designed for Internet Service Providers to manage customers, billing, network connectivity, payments, resellers, support, notifications, analytics, and value-added media services from a single platform.

BeeCore must support both:

- **Basic/manual ISP operations** for smaller providers
- **Advanced/automated ISP operations** for larger providers

The system must be modular so an ISP can start with basic billing and gradually enable network automation, payment gateways, reseller management, customer applications, media services, and advanced analytics.

The core business model is subscription-based SaaS with customer-volume pricing and optional paid add-ons such as SMS, email, white-labeling, media/storage, and advanced integrations.

---

# 2. Product Vision

## Vision

Build a modern ISP operating platform that connects:

**Customer → Billing → Payment → Network → Support → Reseller → Notification**

into one ecosystem.

## Core Promise

> From customer registration to billing, payment, internet activation, suspension, reconnection, support, and customer engagement — everything in one platform.

---

# 3. Target Customers

## Primary

- Local ISPs
- Regional ISPs
- FTTH providers
- Broadband providers
- Small and medium ISP businesses
- Large ISP operators
- ISP reseller networks

## Future

- International broadband operators
- Managed Wi-Fi providers
- Community networks
- Enterprise connectivity providers

---

# 4. Core Product Modules

BeeCore consists of the following major modules:

1. Multi-Tenant SaaS
2. ISP Management
3. Customer Management
4. Billing & Invoice
5. Payment Management
6. Network Management
7. Auto Activation/Suspension
8. Reseller Management
9. Customer App
10. Support & Ticketing
11. Notification Engine
12. SMS/Email Add-ons
13. Multi-Language
14. Multi-Currency
15. Media/Movie Server
16. Reports & Analytics
17. SaaS Subscription Management
18. White-Label System
19. Audit & Security
20. Super Admin Platform

---

# 5. Multi-Tenant SaaS Architecture

Every ISP is an independent tenant.

Example:

```text
BeeCore Platform
│
├── ISP A
│   ├── Customers
│   ├── Staff
│   ├── Resellers
│   ├── Billing
│   ├── Payments
│   └── Network
│
├── ISP B
│   ├── Customers
│   ├── Staff
│   ├── Resellers
│   ├── Billing
│   ├── Payments
│   └── Network
│
└── ISP C
    ├── Customers
    ├── Staff
    ├── Resellers
    ├── Billing
    ├── Payments
    └── Network
```

## Tenant Isolation Requirements

Every tenant-owned database record must contain a tenant identifier or equivalent isolation mechanism.

The system must prevent:

- Cross-tenant customer access
- Cross-tenant billing access
- Cross-tenant payment access
- Cross-tenant API credential access
- Cross-tenant staff access
- Cross-tenant file/media access

Tenant isolation must be enforced at the backend/API layer, not only in the UI.

---

# 6. ISP Hierarchy

Recommended internal structure:

```text
ISP
│
├── Zones
│   │
│   ├── Area
│   │   ├── POP
│   │   │   ├── Router
│   │   │   ├── MikroTik
│   │   │   ├── OLT
│   │   │   └── Customers
│   │   │
│   │   └── Customers
│   │
│   └── POP
│
├── Resellers
├── Staff
└── Customers
```

A customer may be associated with:

- Zone
- Area
- POP
- Network device
- Connection type
- Package
- Reseller

---

# 7. User Roles

## 7.1 BeeCore Super Admin

Managed by the SaaS company.

Permissions:

- Manage ISP tenants
- Manage SaaS plans
- Manage subscriptions
- View platform analytics
- Manage add-ons
- Manage system settings
- Manage integrations
- Monitor system health
- Manage support
- Manage billing for SaaS subscriptions

Super Admin must not automatically have access to sensitive ISP customer data unless explicitly authorized.

---

## 7.2 ISP Owner

Full access to the ISP tenant.

Can manage:

- Customers
- Billing
- Packages
- Payments
- Staff
- Resellers
- Network
- Reports
- Settings
- Integrations

---

## 7.3 ISP Administrator

Configurable permissions.

---

## 7.4 Finance/Accounts

Can manage:

- Bills
- Invoices
- Payments
- Discounts
- Adjustments
- Collections
- Financial reports

No network configuration access by default.

---

## 7.5 Support Agent

Can manage:

- Customer profiles
- Tickets
- Complaints
- Service status
- Communication

---

## 7.6 Network Engineer

Can manage:

- Network devices
- MikroTik/API integrations
- OLT
- PPPoE
- DHCP/IPoE
- Static IP
- Packages
- Bandwidth
- Network status

---

## 7.7 Reseller

Can manage only assigned customers and permitted functions.

---

## 7.8 Customer

Can access only their own:

- Profile
- Package
- Bill
- Payment
- Connection status
- Tickets
- Notifications
- Media

---

# 8. Permission System

Permissions should be granular.

Example:

```text
customers.view
customers.create
customers.edit
customers.delete

billing.view
billing.create
billing.edit
billing.refund

payments.view
payments.create
payments.verify

network.view
network.manage
network.activate
network.suspend

reseller.view
reseller.manage

reports.view
reports.export
```

Roles should be collections of permissions.

---

# 9. Customer Management

Customer profile should support:

## Personal

- Customer ID
- Name
- Mobile
- Email
- NID/optional identification field
- Address
- Profile photo (optional)
- Notes

## Location

- Zone
- Area
- POP
- Address
- Geo-location (optional)

## Connection

- Connection type
- Username
- Password reference (never store unnecessarily in plaintext)
- IP address
- MAC address
- VLAN
- ONU/ONT serial
- PON port
- Network device
- Router
- Service status

## Billing

- Package
- Billing cycle
- Monthly fee
- Due date
- Grace period
- Discount
- Tax/VAT if applicable
- Balance

## Service Status

- Pending
- Active
- Grace
- Suspended
- Terminated
- Blocked

---

# 10. Supported Connection Models

BeeCore must be designed to support mixed ISP environments.

Potential connection types:

- PPPoE
- DHCP/IPoE
- Static IP
- FTTH
- Other/custom API-controlled connection

The connection type determines how activation and suspension are executed.

---

# 11. Network Integration Engine

Network integrations must be modular.

```text
Network Integration Engine
│
├── MikroTik
├── RADIUS
├── OLT
├── DHCP/IPoE
├── PPPoE
├── Static IP
└── Custom API
```

The system must not hard-code one vendor into the billing engine.

Instead:

```text
Billing Rule
     ↓
Network Action
     ↓
Integration Adapter
     ↓
Network Device/API
```

---

# 12. Auto Activation & Suspension

## Suspension Workflow

```text
Invoice Generated
        ↓
Due Date
        ↓
Grace Period
        ↓
Payment Not Received
        ↓
Suspension Rule
        ↓
Network Integration
        ↓
Suspend Customer
        ↓
Notification
```

## Activation Workflow

```text
Payment Received
        ↓
Payment Verification
        ↓
Invoice Updated
        ↓
Account Eligible
        ↓
Network Integration
        ↓
Activate Customer
        ↓
Notification
```

## Manual Override

Every automated network action must support manual override by authorized staff.

Example:

```text
Auto Suspension Failed
        ↓
Admin Alert
        ↓
Manual Suspend
```

---

# 13. Network Action Queue

Network actions should be queued rather than executed directly inside the billing transaction.

Example:

```text
Payment Confirmed
      ↓
Create Activation Job
      ↓
Queue
      ↓
Network Worker
      ↓
API
      ↓
Success / Failure
```

Benefits:

- Retry
- Failure tracking
- Scalability
- Audit trail
- Avoid blocking billing transactions

---

# 14. POP & Network Device Management

ISP can create:

- Zones
- Areas
- POPs
- Routers
- MikroTik devices
- OLTs
- Other network devices

Each device should have:

- Name
- Vendor
- Model
- IP/hostname
- API type
- Connection status
- Credentials
- Assigned POP
- Health status

Credentials must be encrypted at rest.

---

# 15. Billing Engine

Features:

- Automatic invoice generation
- Manual invoice
- Monthly billing
- Quarterly billing
- Half-yearly billing
- Yearly billing
- Custom billing
- Partial payment
- Advance payment
- Discount
- Late fee
- Adjustment
- Credit balance
- Refund
- Multiple services

---

# 16. Billing Rules

Each ISP can configure:

- Billing date
- Due date
- Grace period
- Suspension date
- Late fee
- Reconnection fee
- Auto suspension
- Auto activation
- Reminder schedule

Example:

```text
Bill: 1 August
Due: 10 August
Grace: 3 days
Suspend: 14 August
```

---

# 17. Package Management

Package fields:

- Package name
- Speed
- Download speed
- Upload speed
- Monthly price
- Setup fee
- VAT/tax
- Billing cycle
- Status
- Network profile
- Reseller price
- Customer price

Example:

```text
20 Mbps
Customer Price: ৳500
Reseller Price: ৳450
Network Profile: 20M
```

---

# 18. Payment System

Payment architecture must support multiple payment providers.

Possible methods:

- bKash
- Nagad
- Rocket
- Card
- Bank transfer
- Internet banking
- QR
- Cash
- POS
- Custom gateway

Gateway architecture:

```text
Payment Engine
│
├── Gateway A
├── Gateway B
├── Gateway C
├── Bank
└── Manual
```

Each ISP can configure its own supported payment methods.

---

# 19. Payment Webhook System

For supported gateways:

```text
Customer Payment
       ↓
Gateway
       ↓
Webhook
       ↓
BeeCore
       ↓
Verify Transaction
       ↓
Update Payment
       ↓
Update Invoice
       ↓
Activate Service
```

Webhook processing must be idempotent to prevent duplicate payments.

---

# 20. Reseller Management

Reseller hierarchy:

```text
ISP
│
├── Reseller A
│   ├── Customer 1
│   ├── Customer 2
│   └── Customer 3
│
└── Reseller B
    ├── Customer 4
    └── Customer 5
```

Reseller features:

- Customer registration
- Package assignment
- Payment collection
- Customer status
- Billing
- Commission
- Wallet
- Reports
- Sales history

---

# 21. Reseller Wallet

Possible models:

## Prepaid

```text
Reseller Wallet
      ↓
Customer activation
      ↓
Balance deduction
```

## Commission

```text
Customer Payment
      ↓
Reseller Commission
      ↓
Wallet
```

The ISP can choose the preferred model.

---

# 22. Customer Mobile App

Main dashboard:

```text
Internet
● Active

Package
20 Mbps

Current Bill
৳500

Due Date
18 August

[Pay Now]
```

Main sections:

- Home
- Bills
- Payments
- Package
- Internet status
- Support
- Notifications
- Movies/Media
- Profile

---

# 23. Customer Payment Experience

Customer should be able to:

1. View current bill
2. Select payment method
3. Complete payment
4. Receive confirmation
5. See updated balance
6. Automatically regain service where automation is enabled

---

# 24. Support & Ticket System

Ticket statuses:

```text
Open
↓
Assigned
↓
In Progress
↓
Resolved
↓
Closed
```

Ticket fields:

- Customer
- Category
- Priority
- Description
- Attachment
- Assigned agent
- SLA
- Status
- Internal notes

Possible categories:

- Internet down
- Slow speed
- Payment issue
- Billing issue
- Connection request
- Package change
- Technical issue
- Other

---

# 25. Notification Engine

Channels:

- SMS
- Email
- Push notification
- In-app
- Future: WhatsApp

Triggers:

- New customer
- Account activated
- Invoice generated
- Payment reminder
- Due today
- Overdue
- Suspension warning
- Suspended
- Payment received
- Payment failed
- Service reactivated
- Package changed
- Ticket update

---

# 26. Notification Add-On Revenue

SMS should be usage-based.

Example:

```text
ISP SMS Wallet
Balance: ৳1,000

SMS sent
↓
Usage deducted
```

Actual SMS pricing should be based on the selected SMS provider's cost plus BeeCore's margin.

Email may have:

- Included transactional email
- Paid bulk email
- Higher-volume plans

Push notifications should generally be included where technically practical.

---

# 27. Multi-Language

Initial languages:

- Bangla
- English

Architecture must support future languages without code changes.

All UI text must use translation keys.

Example:

```text
billing.invoice_due
```

Translations:

```text
English: Invoice due date
Bangla: বিল পরিশোধের শেষ তারিখ
```

Customer can select language.

ISP can set default language.

---

# 28. Multi-Currency

Primary:

- BDT

Architecture should support:

- USD
- MYR
- EUR
- Other currencies

Each tenant should define its billing currency.

Currency formatting must be locale-aware.

---

# 29. Media / Movie Server

Media is a value-added module.

Features:

- Content management
- Poster
- Title
- Description
- Genre
- Language
- Subtitle
- Trailer
- Featured content
- Categories
- Search
- Streaming quality
- Watch history

Important:

Only content that the ISP/platform has the legal right or license to distribute should be hosted or streamed.

---

# 30. SaaS Subscription Plans

Recommended commercial model:

## Basic

Starting around:

**৳999/month**

Example inclusion:

- Up to 300 customers
- Customer management
- Billing
- Invoice
- Manual activation
- Basic reports

## Professional

Starting around:

**৳1,999/month**

Example inclusion:

- Up to 1,000 customers
- Advanced billing
- Automation
- API integrations
- Reseller
- Payment gateway
- Advanced reports

## Enterprise

Custom pricing:

- Large customer base
- Dedicated infrastructure
- Custom integrations
- White-label
- Priority support
- SLA

These prices are initial commercial hypotheses and should be validated against actual competitor pricing, infrastructure costs, support costs, and customer willingness to pay before launch.

---

# 31. Customer-Based Pricing

An alternative or hybrid model can be used.

Indicative target:

| Customer Count | Target Effective Price |
|---:|---:|
| 1–500 | ৳3.00/customer |
| 501–2,000 | ৳2.50/customer |
| 2,001–5,000 | ৳2.00/customer |
| 5,001–10,000 | ৳1.50/customer |
| 10,001+ | ৳1.00–৳1.25/customer |

A minimum monthly subscription should apply.

Final pricing should be tested before commercial launch.

---

# 32. Add-On Revenue

Potential add-ons:

- SMS credits
- Bulk email
- White-label
- Custom domain
- Branded mobile app
- Advanced network integrations
- Media server
- Additional storage
- Additional staff seats
- Premium support
- Dedicated infrastructure
- Custom development/integration

---

# 33. White-Label

Enterprise customers can have:

- Custom logo
- Custom brand colors
- Custom domain
- Custom app branding
- Custom email sender
- Custom SMS sender where supported
- Custom login page

---

# 34. SaaS Owner Dashboard

Metrics:

- Total tenants
- Active tenants
- Trial tenants
- Suspended tenants
- Total customers
- Total MRR
- Add-on revenue
- SMS revenue
- Subscription revenue
- Churn
- Trial conversion
- API usage
- Storage usage
- System health

Tenant view:

```text
ISP
Customers: 12,430
Plan: Professional
MRR: ৳18,645
SMS Usage: 48,200
Storage: 320 GB
API Requests: 1.2M
Status: Active
```

---

# 35. SaaS Billing

ISP subscription lifecycle:

```text
Trial
 ↓
Active
 ↓
Payment Due
 ↓
Grace Period
 ↓
Restricted
 ↓
Suspended
```

Never immediately delete tenant data after suspension.

Provide a defined retention period.

---

# 36. Reporting

ISP reports:

### Customer

- Total customers
- New customers
- Active
- Suspended
- Terminated
- Churn

### Billing

- Invoiced
- Paid
- Due
- Overdue
- Discounts
- Refunds
- Collection

### Network

- Online
- Offline
- Suspended
- Device health
- POP status

### Reseller

- Sales
- Collection
- Commission
- Outstanding
- Customer count

### Finance

- Revenue
- Expense fields if enabled
- Payment method
- Gateway performance

---

# 37. Audit Logs

Every important action must be logged.

Example:

```text
User: Admin
Action: Suspend Customer
Customer: CUST-10245
Method: Manual
Time: 2026-08-16 12:30
Result: Success
```

Log:

- Login
- Logout
- Customer changes
- Billing changes
- Payment actions
- Refunds
- Network actions
- Permission changes
- API changes
- Configuration changes

---

# 38. Security Requirements

Must-have:

- Tenant isolation
- HTTPS
- Encryption at rest for secrets
- Secure password hashing
- 2FA option
- Role-based access control
- API authentication
- Rate limiting
- CSRF protection where applicable
- Input validation
- File validation
- Audit logs
- Backup
- Disaster recovery
- Session management
- Credential encryption
- Secret rotation support

Never expose:

- API keys
- Network credentials
- Payment secrets
- Passwords

in client-side code.

---

# 39. API Architecture

BeeCore should expose a versioned API.

Example:

```text
/api/v1/customers
/api/v1/billing
/api/v1/payments
/api/v1/network
/api/v1/resellers
/api/v1/tickets
/api/v1/notifications
```

Use:

- Authentication
- Authorization
- Rate limits
- Pagination
- Filtering
- Sorting
- Error standards
- API versioning

---

# 40. Integration Adapter Pattern

Third-party integrations must use adapters.

Example:

```text
NetworkService
      ↓
MikroTikAdapter
RADIUSAdapter
OLTAdapter
CustomAPIAdapter
```

This prevents the core billing system from depending on one vendor.

---

# 41. Background Jobs

Use background workers for:

- Invoice generation
- SMS
- Email
- Payment verification
- Network actions
- Suspension jobs
- Activation jobs
- Reports
- Media processing
- Notifications

Long-running operations should not block normal web requests.

---

# 42. Reliability

Network automation must be fault tolerant.

Requirements:

- Retry mechanism
- Exponential backoff where appropriate
- Job status
- Failed job queue
- Manual retry
- Alerts
- Audit trail

Example:

```text
Suspend Job
 ↓
API Failed
 ↓
Retry 1
 ↓
Retry 2
 ↓
Retry 3
 ↓
Failed
 ↓
Admin Alert
```

---

# 43. Customer Service Safety

Automation must not accidentally disconnect the wrong customer.

Before network action:

- Validate tenant
- Validate customer
- Validate service
- Validate network mapping
- Validate current state
- Create audit record

Critical actions should support idempotency.

---

# 44. Basic vs Advanced Feature Model

Every tenant can select operational mode.

## Basic

- Customer
- Package
- Billing
- Manual payment
- Manual activation
- Reports

## Advanced

Everything in Basic plus:

- Network API
- Auto suspension
- Auto activation
- Payment gateway
- Reseller
- POP management
- Advanced reports
- Notification automation

Feature flags should control modules.

---

# 45. Recommended MVP

Do not build every feature in version 1.

## MVP Phase 1

### Platform

- Multi-tenancy
- Authentication
- Roles
- Permissions

### ISP

- ISP profile
- Zones
- Areas
- POPs
- Packages

### Customer

- Customer CRUD
- Connection data
- Status

### Billing

- Invoice
- Due
- Payment
- Manual activation/deactivation

### Customer

- Customer portal/app
- Bills
- Payment history
- Notifications

### Reports

- Basic dashboard
- Billing report
- Customer report

### Languages

- Bangla
- English

---

# 46. Phase 2

- MikroTik/API
- Auto suspension
- Auto activation
- Payment gateways
- SMS
- Email
- Reseller
- POP/device management
- Advanced reports
- Audit logs

---

# 47. Phase 3

- RADIUS
- OLT integrations
- Advanced network automation
- White-label
- Custom domains
- Branded apps
- Media server
- Streaming
- Advanced analytics

---

# 48. Phase 4

Potential future features:

- WhatsApp integration
- AI support assistant
- Network anomaly detection
- Predictive churn
- Automated troubleshooting
- Advanced financial analytics
- International payment providers
- International localization

---

# 49. Suggested Product Navigation

## Super Admin

```text
Dashboard
Tenants
Subscriptions
Plans
Add-ons
Payments
Usage
System Health
Integrations
Support
Settings
Audit Logs
```

## ISP Admin

```text
Dashboard
Customers
Packages
Billing
Payments
Resellers
Network
Zones
POP
Devices
Tickets
Notifications
Reports
Media
Staff
Settings
```

## Customer

```text
Home
My Internet
Bills
Payments
Package
Support
Notifications
Media
Profile
```

## Reseller

```text
Dashboard
Customers
Packages
Sales
Payments
Wallet
Commission
Reports
Profile
```

---

# 50. Business Model

BeeCore revenue streams:

1. Monthly SaaS subscription
2. Customer-volume charges
3. SMS usage
4. Email/bulk messaging
5. White-label
6. Custom domain
7. Branded app
8. Media/storage
9. Premium integrations
10. Dedicated infrastructure
11. Premium support
12. Custom development

Goal:

**Low entry barrier + increasing revenue as ISP grows.**

---

# 51. Key Product Differentiators

BeeCore should compete on:

- Affordable Bangladesh pricing
- Multi-tenant architecture
- Manual + automatic operation
- Network automation
- Reseller management
- Customer app
- Multiple payment methods
- Bangla + English
- Modular add-ons
- White-label
- Modern UI
- API-first integrations
- Scalable architecture

---

# 52. Core Customer Journey

```text
ISP signs up
     ↓
Create ISP profile
     ↓
Configure packages
     ↓
Create zones/POP
     ↓
Configure network
     ↓
Add customers
     ↓
Generate bills
     ↓
Customer receives notification
     ↓
Customer pays
     ↓
Payment verified
     ↓
Network activated/maintained
     ↓
Monthly billing continues
```

---

# 53. Critical Design Principle

Billing and network control must remain logically separate.

```text
Billing Engine
       ↓
Service State
       ↓
Network Action
       ↓
Integration Adapter
       ↓
Network Device
```

This allows BeeCore to support different ISP architectures without rebuilding the billing system.

---

# 54. Final Product Architecture

```text
                         BEecore SaaS
                              │
          ┌───────────────────┼───────────────────┐
          │                   │                   │
      ISP Admin          Customer App        Reseller
          │                   │                   │
          └───────────────────┼───────────────────┘
                              │
                         API Gateway
                              │
       ┌──────────────┬───────┼────────┬──────────────┐
       │              │       │        │              │
    Customer       Billing  Payment  Network      Support
     Service       Engine   Engine   Engine       Engine
       │              │       │        │              │
       └──────────────┴───────┼────────┴──────────────┘
                              │
                       Integration Layer
                              │
             ┌────────────────┼────────────────┐
             │                │                │
          MikroTik          RADIUS            OLT
             │
        Custom APIs
                              │
                       Notification Engine
                         │           │
                        SMS        Email/Push

                              │
                         Media Engine
                              │
                         Media Server
```

---

# 55. Success Metrics

After launch, track:

- Number of active ISPs
- Customer count per ISP
- MRR
- ARPU
- Churn
- Trial conversion
- Payment success rate
- SMS usage
- Automation success rate
- Network action failure rate
- Customer app active users
- Support resolution time
- Reseller activity

---

# 56. Final Product Statement

**BeeCore is not simply an ISP billing application.**

It is a modular, multi-tenant SaaS operating platform for ISPs that combines:

> **Customer Management + Billing + Payments + Network Automation + Reseller Management + Customer App + Support + Notifications + Analytics + Media**

with a pricing model designed to remain competitive in the Bangladesh ISP market while creating recurring SaaS and add-on revenue.

---

# 57. Development Rule

Before writing production code:

1. Finalize database schema
2. Finalize tenant isolation strategy
3. Finalize role/permission matrix
4. Finalize billing state machine
5. Finalize network integration interface
6. Finalize payment gateway abstraction
7. Finalize notification abstraction
8. Finalize API contract
9. Finalize MVP scope
10. Build automated tests for critical billing/network workflows

The highest-risk areas are:

- Tenant isolation
- Billing calculations
- Payment verification
- Network activation/suspension
- API failures
- Duplicate webhook/payment events
- Credential security

These must receive priority during architecture and QA.
