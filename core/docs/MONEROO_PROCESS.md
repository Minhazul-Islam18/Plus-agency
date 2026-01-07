# Moneroo Payment Gateway - Working Process Documentation

## Table of Contents

1. [Process Overview](#process-overview)
2. [Course Payment Process](#course-payment-process)
3. [Product Checkout Process](#product-checkout-process)
4. [Package Subscription Process](#package-subscription-process)
5. [Admin Configuration Process](#admin-configuration-process)
6. [Error Handling Process](#error-handling-process)
7. [Webhook/Callback Process](#webhook-callback-process)
8. [Session Management](#session-management)

---

## Process Overview

### Complete Payment Lifecycle

```
╔════════════════════════════════════════════════════════════════╗
║                   MONEROO PAYMENT LIFECYCLE                     ║
╚════════════════════════════════════════════════════════════════╝

    ┌─────────────┐
    │   START     │
    │  (User)     │
    └──────┬──────┘
           │
           ▼
    ┌─────────────────────┐
    │ Select Item/Service │
    │ (Course/Product/    │
    │  Package/Donation)  │
    └──────┬──────────────┘
           │
           ▼
    ┌─────────────────────┐
    │ Choose Payment      │
    │ Method: Moneroo     │
    └──────┬──────────────┘
           │
           ▼
    ┌─────────────────────┐
    │ Fill Billing Info   │
    │ (if required)       │
    └──────┬──────────────┘
           │
           ▼
    ╔═══════════════════════╗
    ║  PAYMENT INIT PHASE   ║
    ╚═══════════════════════╝
           │
           ▼
    ┌─────────────────────┐
    │ Validate Form Data  │
    └──────┬──────────────┘
           │
           ▼
    ┌─────────────────────┐
    │ Check User Auth     │
    │ (if required)       │
    └──────┬──────────────┘
           │
           ▼
    ┌─────────────────────┐
    │ Calculate Amount    │
    │ + Currency Convert  │
    └──────┬──────────────┘
           │
           ▼
    ┌─────────────────────┐
    │ Call Moneroo API:   │
    │ Payment::init()     │
    └──────┬──────────────┘
           │
           ▼
    ┌─────────────────────┐      ┌────────────┐
    │ API Success?        │─NO──→│ Show Error │──→ END
    └──────┬──────────────┘      └────────────┘
           │ YES
           ▼
    ┌─────────────────────┐
    │ Store Session Data: │
    │ - Transaction ID    │
    │ - Order Data        │
    │ - Currency          │
    └──────┬──────────────┘
           │
           ▼
    ┌─────────────────────┐
    │ Redirect User to    │
    │ Moneroo Checkout    │
    └──────┬──────────────┘
           │
           ▼
    ╔═══════════════════════╗
    ║  MONEROO CHECKOUT     ║
    ╚═══════════════════════╝
           │
           ▼
    ┌─────────────────────┐
    │ User Enters Payment │
    │ Details on Moneroo  │
    └──────┬──────────────┘
           │
           ▼
    ┌─────────────────────┐
    │ Moneroo Processes   │
    │ Payment with        │
    │ Payment Provider    │
    └──────┬──────────────┘
           │
           ▼
    ┌─────────────────────┐
    │ Payment Success?    │
    └──────┬──────────────┘
           │
     ┌─────┴─────┐
     │           │
    YES         NO
     │           │
     ▼           ▼
┌────────┐  ┌────────┐
│Success │  │ Cancel │
│ Flow   │  │  Flow  │
└───┬────┘  └───┬────┘
    │           │
    ▼           ▼
╔═══════════════════════╗
║  CALLBACK/NOTIFY      ║
╚═══════════════════════╝
    │
    ▼
┌─────────────────────┐
│ User Redirected     │
│ Back to Website     │
└──────┬──────────────┘
       │
       ▼
┌─────────────────────┐
│ Retrieve Session    │
│ Data (Txn ID, etc)  │
└──────┬──────────────┘
       │
       ▼
┌─────────────────────┐
│ Call Moneroo API:   │
│ Payment::verify()   │
└──────┬──────────────┘
       │
       ▼
┌─────────────────────┐      ┌────────────┐
│ Payment Verified?   │─NO──→│   Cancel   │──→ END
└──────┬──────────────┘      │    Page    │
       │ YES                 └────────────┘
       ▼
╔═══════════════════════╗
║  ORDER COMPLETION     ║
╚═══════════════════════╝
       │
       ▼
┌─────────────────────┐
│ Create Order/       │
│ Purchase Record     │
└──────┬──────────────┘
       │
       ▼
┌─────────────────────┐
│ Generate PDF        │
│ Invoice             │
└──────┬──────────────┘
       │
       ▼
┌─────────────────────┐
│ Send Email          │
│ Confirmation        │
└──────┬──────────────┘
       │
       ▼
┌─────────────────────┐
│ Clear Session Data  │
└──────┬──────────────┘
       │
       ▼
┌─────────────────────┐
│ Redirect to         │
│ Success Page        │
└──────┬──────────────┘
       │
       ▼
   ┌────────┐
   │  END   │
   └────────┘
```

---

## Course Payment Process

### Detailed Course Enrollment Flow

```
╔════════════════════════════════════════════════════════════════╗
║              COURSE ENROLLMENT WITH MONEROO                     ║
╚════════════════════════════════════════════════════════════════╝

┌─────────────┐
│ User Views  │
│ Course Page │
└──────┬──────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Click "Enroll Now" Button            │
│ - Shows enrollment form               │
│ - Payment gateway dropdown            │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Select "Moneroo" from Dropdown       │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Submit Form                          │
│ POST /course/payment/moneroo         │
│ Data: course_id                      │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║ MonerooGatewayController::redirectToMoneroo()            ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ 1. Authentication Check              │
│    if (!Auth::user()) {              │
│        redirect to login             │
│    }                                 │
└──────┬───────────────────────────────┘
       │ USER IS AUTHENTICATED
       ▼
┌──────────────────────────────────────┐
│ 2. Load Course Data                  │
│    $course = Course::find(id)        │
│    $price = $course->current_price   │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 3. Get Language Settings             │
│    $currentLang = Language::...      │
│    $bse = $currentLang->basic_extra  │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 4. Currency Conversion               │
│    if (currency != 'USD') {          │
│        $price = $price / rate        │
│    }                                 │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 5. Initialize Moneroo Payment        │
│    Payment::init([                   │
│        'amount' => $price,           │
│        'currency' => 'USD',          │
│        'customer' => [               │
│            'email' => user email,    │
│            'first_name' => fname,    │
│            'last_name' => lname      │
│        ],                            │
│        'return_url' => notify URL,   │
│        'description' => course title │
│    ])                                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐     ┌────────────────┐
│ 6. API Call Success?                 │─NO─→│ Show Error Msg │
└──────┬───────────────────────────────┘     │ Redirect Back  │
       │ YES                                  └────────────────┘
       ▼
┌──────────────────────────────────────┐
│ 7. Store in Session                  │
│    Session::put([                    │
│        'courseData' => $course,      │
│        'currency' => $currency,      │
│        'monerooTransactionId' => id  │
│    ])                                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 8. Redirect to Moneroo Checkout      │
│    return redirect()->away(          │
│        $payment['checkout_url']      │
│    )                                 │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║               USER AT MONEROO CHECKOUT                    ║
║  - User enters payment details                            ║
║  - Selects payment method (mobile money, card, etc.)      ║
║  - Completes payment                                      ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ Moneroo Redirects Back               │
│ GET /course/payment/moneroo/notify   │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║ MonerooGatewayController::notify()                       ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ 1. Retrieve Session Data             │
│    $courseInfo = Session::get(...)   │
│    $transactionId = Session::get(...) │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐     ┌────────────────┐
│ 2. Transaction ID Exists?            │─NO─→│ Redirect to    │
└──────┬───────────────────────────────┘     │ Cancel Page    │
       │ YES                                  └────────────────┘
       ▼
┌──────────────────────────────────────┐
│ 3. Verify Payment with Moneroo       │
│    $verification = Payment::verify(  │
│        $transactionId                │
│    )                                 │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐     ┌────────────────┐
│ 4. Payment Status = Success?         │─NO─→│ Redirect to    │
└──────┬───────────────────────────────┘     │ Cancel Page    │
       │ YES                                  └────────────────┘
       ▼
┌──────────────────────────────────────┐
│ 5. Create Course Purchase Record     │
│    CoursePurchase::create([          │
│        'user_id' => Auth::user()->id,│
│        'course_id' => course id,     │
│        'order_number' => generate,   │
│        'payment_method' => 'moneroo',│
│        'payment_status' => 'Completed'│
│    ])                                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 6. Generate PDF Invoice              │
│    $fileName = order_number.pdf      │
│    PDF::loadView('pdf.course',...)   │
│       ->save($fileName)              │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 7. Update Invoice in Database        │
│    $purchase->update([               │
│        'invoice' => $fileName        │
│    ])                                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 8. Send Confirmation Email           │
│    MailController::sendMail(...)     │
│    - Attach invoice PDF              │
│    - Send to user                    │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 9. Clear Session Data                │
│    Session::forget([                 │
│        'courseData',                 │
│        'currency',                   │
│        'monerooTransactionId'        │
│    ])                                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 10. Redirect to Success Page         │
│     return redirect()->route(        │
│         'course.moneroo.complete'    │
│     )                                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ SUCCESS PAGE DISPLAYED                │
│ - "Payment Successful"                │
│ - Course access granted               │
│ - Download invoice link               │
└───────────────────────────────────────┘
```

### Code Flow Example

```php
// STEP 1: User submits form
POST /course/payment/moneroo
{ course_id: 123 }

// STEP 2: Controller processes
MonerooGatewayController::redirectToMoneroo()
├── Check authentication
├── Load course ($99.99)
├── Convert currency (if needed)
├── Call Moneroo API
│   └── Payment::init([
│         'amount' => 99.99,
│         'currency' => 'USD',
│         'customer' => [...],
│         'return_url' => 'https://site.com/course/payment/moneroo/notify',
│         'description' => 'Course: Laravel Advanced'
│       ])
├── Response: {
│     'transaction_id' => 'txn_abc123',
│     'checkout_url' => 'https://checkout.moneroo.io/pay/txn_abc123'
│   }
├── Store in session:
│   ├── courseData: Course object
│   ├── currency: 'USD'
│   └── monerooTransactionId: 'txn_abc123'
└── Redirect to: https://checkout.moneroo.io/pay/txn_abc123

// STEP 3: User pays on Moneroo
// ... User completes payment ...

// STEP 4: Moneroo redirects back
GET /course/payment/moneroo/notify

// STEP 5: Verify and complete
MonerooGatewayController::notify()
├── Get session data
├── Verify: Payment::verify('txn_abc123')
├── Response: { 'status' => 'success' }
├── Save purchase:
│   └── CoursePurchase::create([...])
├── Generate invoice PDF
├── Send email
├── Clear session
└── Redirect to success page
```

---

## Product Checkout Process

### Detailed Product Checkout Flow

```
╔════════════════════════════════════════════════════════════════╗
║              PRODUCT CHECKOUT WITH MONEROO                      ║
╚════════════════════════════════════════════════════════════════╝

┌─────────────┐
│ User Browses│
│ Products    │
└──────┬──────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Add Products to Cart                 │
│ Session::put('cart', [               │
│    product_id => [                   │
│        'qty' => 2,                   │
│        'price' => 49.99              │
│    ]                                 │
│ ])                                   │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Navigate to /checkout                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Fill Checkout Form:                  │
│ ┌────────────────────────────────┐   │
│ │ Billing Information:           │   │
│ │ - First Name                   │   │
│ │ - Last Name                    │   │
│ │ - Email                        │   │
│ │ - Address                      │   │
│ │ - City, Country                │   │
│ │ - Phone Number                 │   │
│ ├────────────────────────────────┤   │
│ │ Shipping Information:          │   │
│ │ - Same as billing OR           │   │
│ │ - Different shipping address   │   │
│ ├────────────────────────────────┤   │
│ │ Shipping Method:               │   │
│ │ ○ Standard ($5)                │   │
│ │ ○ Express ($15)                │   │
│ │ ○ Free Shipping                │   │
│ ├────────────────────────────────┤   │
│ │ Payment Method:                │   │
│ │ ○ Moneroo ← Selected          │   │
│ │ ○ Stripe                       │   │
│ │ ○ PayPal                       │   │
│ └────────────────────────────────┘   │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Submit Checkout Form                 │
│ POST /product/moneroo/submit         │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║ MonerooController::store() (Product)                     ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐     ┌────────────────┐
│ 1. Check Cart Exists                 │─NO─→│ Return 404     │
│    if (!Session::has('cart'))        │     └────────────────┘
└──────┬───────────────────────────────┘
       │ YES
       ▼
┌──────────────────────────────────────┐
│ 2. Validate Form Data                │
│    $this->orderValidation($request)  │
│    - Required fields                 │
│    - Email format                    │
│    - Phone format                    │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 3. Calculate Order Total             │
│    $total = $this->orderTotal(       │
│        $request->shipping_charge     │
│    )                                 │
│    ┌──────────────────────────┐     │
│    │ Cart Total:      $99.98  │     │
│    │ Tax:             $8.00   │     │
│    │ Discount:       -$10.00  │     │
│    │ Shipping:        $15.00  │     │
│    │ ─────────────────────── │     │
│    │ TOTAL:          $112.98  │     │
│    └──────────────────────────┘     │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 4. Currency Conversion               │
│    $total = round(                   │
│        $total / $bex->currency_rate  │
│    , 2)                              │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 5. Initialize Moneroo Payment        │
│    Payment::init([                   │
│        'amount' => $total,           │
│        'currency' => 'USD',          │
│        'customer' => [               │
│            'email' => billing email, │
│            'first_name' => fname,    │
│            'last_name' => lname,     │
│            'phone' => phone,         │
│            'address' => address,     │
│            'city' => city,           │
│            'country' => country      │
│        ],                            │
│        'return_url' => notify URL,   │
│        'description' => 'Product Order'│
│    ])                                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐     ┌────────────────┐
│ 6. API Success?                      │─NO─→│ Show Error     │
└──────┬───────────────────────────────┘     │ Redirect Back  │
       │ YES                                  └────────────────┘
       ▼
┌──────────────────────────────────────┐
│ 7. Store Session Data                │
│    Session::put([                    │
│        'monerooOrderData' => all form│
│        'monerooTransactionId' => id  │
│    ])                                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 8. Redirect to Moneroo               │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║               MONEROO PAYMENT PAGE                        ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ Moneroo Redirects Back               │
│ GET /product/moneroo/notify          │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║ MonerooController::notify() (Product)                    ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ 1. Retrieve Session Data             │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐     ┌────────────────┐
│ 2. Data Exists?                      │─NO─→│ Cancel Page    │
└──────┬───────────────────────────────┘     └────────────────┘
       │ YES
       ▼
┌──────────────────────────────────────┐
│ 3. Verify Payment                    │
│    $verification = Payment::verify() │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐     ┌────────────────┐
│ 4. Payment Success?                  │─NO─→│ Cancel Page    │
└──────┬───────────────────────────────┘     └────────────────┘
       │ YES
       ▼
┌──────────────────────────────────────┐
│ 5. Save Product Order                │
│    $order = $this->saveOrder(        │
│        $request, txnId, chargeId,    │
│        'Completed', 'online'         │
│    )                                 │
│    ┌──────────────────────────┐     │
│    │ ProductOrder Created:    │     │
│    │ - ID: 789                │     │
│    │ - Billing Info           │     │
│    │ - Shipping Info          │     │
│    │ - Total: $112.98         │     │
│    │ - Method: moneroo        │     │
│    │ - Status: Completed      │     │
│    └──────────────────────────┘     │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 6. Save Ordered Items                │
│    $this->saveOrderedItems(order_id) │
│    ┌──────────────────────────┐     │
│    │ OrderItem #1:            │     │
│    │ - Product: Widget A      │     │
│    │ - Quantity: 2            │     │
│    │ - Price: $49.99          │     │
│    ├──────────────────────────┤     │
│    │ OrderItem #2:            │     │
│    │ - Product: Widget B      │     │
│    │ - Quantity: 1            │     │
│    │ - Price: $49.99          │     │
│    └──────────────────────────┘     │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 7. Generate Invoice PDF              │
│    $this->sendMails($order)          │
│    - Create PDF from template        │
│    - Save to invoices folder         │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 8. Send Email Notifications          │
│    - Send to customer                │
│    - Send to admin (BCC)             │
│    - Attach invoice PDF              │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 9. Clear Session Data                │
│    Session::forget([                 │
│        'cart',                       │
│        'coupon',                     │
│        'monerooOrderData',           │
│        'monerooTransactionId'        │
│    ])                                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 10. Redirect to Success Page         │
│     return $this->payreturn()        │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ SUCCESS PAGE                          │
│ ┌────────────────────────────────┐   │
│ │ ✓ Order Successful!            │   │
│ │                                │   │
│ │ Order Number: #78945612        │   │
│ │ Total: $112.98                 │   │
│ │                                │   │
│ │ [Download Invoice]             │   │
│ │ [Track Order]                  │   │
│ └────────────────────────────────┘   │
└───────────────────────────────────────┘
```

---

## Package Subscription Process

### Package Subscription Flow

```
╔════════════════════════════════════════════════════════════════╗
║           PACKAGE SUBSCRIPTION WITH MONEROO                     ║
╚════════════════════════════════════════════════════════════════╝

┌─────────────┐
│ User Views  │
│ Packages    │
└──────┬──────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Select Package                       │
│ - Basic ($9.99/month)                │
│ - Pro ($29.99/month)                 │
│ - Enterprise ($99.99/month) ← Select │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Click "Subscribe" Button             │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Fill Subscription Form:              │
│ ┌────────────────────────────────┐   │
│ │ Name: ____________________    │   │
│ │ Email: ___________________    │   │
│ │                               │   │
│ │ [Dynamic Custom Fields]       │   │
│ │ Company: _________________    │   │
│ │ Tax ID: __________________    │   │
│ │                               │   │
│ │ Payment Method:               │   │
│ │ ● Moneroo                     │   │
│ │ ○ Stripe                      │   │
│ │ ○ PayPal                      │   │
│ └────────────────────────────────┘   │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Submit Form                          │
│ POST /moneroo/submit                 │
│ Data: package_id, name, email, ...   │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║ MonerooController::store() (Package)                     ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ 1. Get Language & Package Data       │
│    $package = Package::find(id)      │
│    $price = $package->price          │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 2. Validate Form Data                │
│    $this->orderValidation(           │
│        $request, $package_inputs     │
│    )                                 │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 3. Create Pending Order              │
│    $po = $this->saveOrder(           │
│        $request,                     │
│        $package_inputs,              │
│        payment_status: 0 (pending)   │
│    )                                 │
│    ┌──────────────────────────┐     │
│    │ PackageOrder Created:    │     │
│    │ - ID: 456                │     │
│    │ - Package: Enterprise    │     │
│    │ - Name: John Doe         │     │
│    │ - Email: john@ex.com     │     │
│    │ - Status: Pending (0)    │     │
│    └──────────────────────────┘     │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 4. Convert Currency                  │
│    $price = $price / currency_rate   │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 5. Initialize Moneroo Payment        │
│    Payment::init([                   │
│        'amount' => $price,           │
│        'currency' => 'USD',          │
│        'customer' => [               │
│            'email' => $request->email│
│            'first_name' => $name     │
│        ],                            │
│        'return_url' => notify URL,   │
│        'description' => package title│
│    ])                                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 6. Store Session Data                │
│    Session::put([                    │
│        'monerooPackageOrderId' => po │
│        'monerooPackageId' => pkg_id  │
│        'monerooTransactionId' => id  │
│    ])                                │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 7. Redirect to Moneroo               │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║               MONEROO PAYMENT                             ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ Callback: GET /moneroo/notify        │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║ MonerooController::notify() (Package)                    ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ 1. Get Session Data                  │
│    $orderId = Session::get(...)      │
│    $transactionId = Session::get(...)│
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 2. Verify Payment                    │
│    $verification = Payment::verify() │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐     ┌────────────────┐
│ 3. Status = Success/Completed?       │─NO─→│ Cancel Page    │
└──────┬───────────────────────────────┘     └────────────────┘
       │ YES
       ▼
┌──────────────────────────────────────┐
│ 4. Update Order Status               │
│    PackageOrder::find($orderId)      │
│        ->update([                    │
│            'payment_status' => 1     │
│        ])                            │
│    ┌──────────────────────────┐     │
│    │ Order #456 Updated:      │     │
│    │ Status: Pending → Complete│     │
│    └──────────────────────────┘     │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 5. Send Confirmation Emails          │
│    $this->sendMails($po, $be, $bex)  │
│    - Generate invoice PDF            │
│    - Email to customer               │
│    - Email to admin                  │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 6. Clear Session                     │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 7. Redirect to Confirmation          │
│    route('front.packageorder.        │
│           confirmation',             │
│           [$packageId, $orderId])    │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ CONFIRMATION PAGE                     │
│ ┌────────────────────────────────┐   │
│ │ ✓ Subscription Activated!      │   │
│ │                                │   │
│ │ Package: Enterprise            │   │
│ │ Billing: $99.99/month          │   │
│ │ Order #: 456                   │   │
│ │                                │   │
│ │ Your subscription is now active│   │
│ │ [Download Invoice]             │   │
│ │ [Go to Dashboard]              │   │
│ └────────────────────────────────┘   │
└───────────────────────────────────────┘
```

---

## Admin Configuration Process

### Admin Gateway Configuration Flow

```
╔════════════════════════════════════════════════════════════════╗
║              ADMIN GATEWAY CONFIGURATION                        ║
╚════════════════════════════════════════════════════════════════╝

┌─────────────┐
│ Admin Login │
└──────┬──────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Navigate to Admin Dashboard          │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Click "Payment Gateways" Menu        │
│ GET /admin/gateways                  │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║ GatewayController::index()                               ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ Load All Gateways from Database      │
│ $data['paypal'] = ...find(15)        │
│ $data['stripe'] = ...find(14)        │
│ $data['moneroo'] = ...find(20)       │
│ ...                                  │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Display Gateway Configuration Page   │
│ ┌────────────────────────────────┐   │
│ │ Payment Gateways               │   │
│ ├────────────────────────────────┤   │
│ │ [Stripe Card] [PayPal Card]    │   │
│ │ [Razorpay Card] [Moneroo Card] │   │
│ │ ...                            │   │
│ └────────────────────────────────┘   │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Admin Finds Moneroo Card             │
│ ┌────────────────────────────────┐   │
│ │ ╔══════════════════════════╗   │   │
│ │ ║       Moneroo           ║   │   │
│ │ ╚══════════════════════════╝   │   │
│ │                               │   │
│ │ Status: ● Active ○ Inactive   │   │
│ │                               │   │
│ │ Public Key:                   │   │
│ │ [___________________]         │   │
│ │                               │   │
│ │ Secret Key:                   │   │
│ │ [___________________]         │   │
│ │                               │   │
│ │ Text: Pay via Moneroo...      │   │
│ │                               │   │
│ │         [Update Button]       │   │
│ └────────────────────────────────┘   │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Admin Updates Configuration:         │
│ - Status: Active                     │
│ - Public Key: pk_live_abc123xyz      │
│ - Secret Key: sk_live_xyz789abc      │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Click "Update" Button                │
│ POST /admin/moneroo/update           │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║ GatewayController::monerooUpdate()                       ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ 1. Load Moneroo Gateway              │
│    $moneroo = PaymentGateway::find(20│
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 2. Update Status                     │
│    $moneroo->status = $request->status│
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 3. Prepare Information JSON          │
│    $information = [                  │
│        'public_key' => pk_live_...,  │
│        'secret_key' => sk_live_...,  │
│        'text' => 'Pay via Moneroo...'│
│    ]                                 │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 4. Save to Database                  │
│    $moneroo->information =           │
│        json_encode($information)     │
│    $moneroo->save()                  │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 5. Update .env File                  │
│    $this->updateEnvVariables(        │
│        $publicKey, $secretKey        │
│    )                                 │
│    ┌──────────────────────────┐     │
│    │ .env file updated:       │     │
│    │ MONEROO_PUBLIC_KEY=pk... │     │
│    │ MONEROO_SECRET_KEY=sk... │     │
│    └──────────────────────────┘     │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 6. Flash Success Message             │
│    Session::flash('success',         │
│        'Moneroo information updated!')│
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 7. Redirect Back                     │
│    return back()                     │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Gateway Page with Success Message    │
│ ┌────────────────────────────────┐   │
│ │ ✓ Moneroo information updated  │   │
│ │   successfully!                │   │
│ └────────────────────────────────┘   │
│                                      │
│ [Moneroo Card now shows updated info]│
└───────────────────────────────────────┘
```

### Environment Variable Update Process

```
┌──────────────────────────────────────┐
│ updateEnvVariables($pubKey, $secKey) │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 1. Get .env File Path                │
│    $envPath = base_path('.env')      │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐     ┌────────────────┐
│ 2. File Exists?                      │─NO─→│ Exit (No Update│
└──────┬───────────────────────────────┘     └────────────────┘
       │ YES
       ▼
┌──────────────────────────────────────┐
│ 3. Read Current Content              │
│    $content = file_get_contents(...)│
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ 4. Check if MONEROO_PUBLIC_KEY Exists│
└──────┬───────────────────────────────┘
       │
   ┌───┴───┐
   │       │
  YES     NO
   │       │
   ▼       ▼
┌──────┐  ┌──────┐
│Replace│  │Append│
│ Line │  │ Line │
└───┬──┘  └───┬──┘
    │         │
    └────┬────┘
         │
         ▼
┌──────────────────────────────────────┐
│ 5. Check if MONEROO_SECRET_KEY Exists│
└──────┬───────────────────────────────┘
       │
   ┌───┴───┐
   │       │
  YES     NO
   │       │
   ▼       ▼
┌──────┐  ┌──────┐
│Replace│  │Append│
│ Line │  │ Line │
└───┬──┘  └───┬──┘
    │         │
    └────┬────┘
         │
         ▼
┌──────────────────────────────────────┐
│ 6. Write Updated Content             │
│    file_put_contents($path, $content)│
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ .env File Updated                    │
│ ┌────────────────────────────────┐   │
│ │ APP_NAME=Laravel               │   │
│ │ APP_ENV=production             │   │
│ │ ...                            │   │
│ │ MONEROO_PUBLIC_KEY=pk_live_abc │   │
│ │ MONEROO_SECRET_KEY=sk_live_xyz │   │
│ └────────────────────────────────┘   │
└───────────────────────────────────────┘
```

---

## Error Handling Process

### Error Handling Flow

```
╔════════════════════════════════════════════════════════════════╗
║                    ERROR HANDLING FLOW                          ║
╚════════════════════════════════════════════════════════════════╝

┌──────────────────────┐
│ Payment Process      │
│ Running...           │
└──────┬───────────────┘
       │
       ▼
    ┌──────────────────┐
    │ Error Occurs?    │
    └──────┬───────────┘
           │
    ┌──────┴──────┐
    │             │
   YES           NO
    │             │
    ▼             ▼
┌────────┐    ┌────────┐
│ Handle │    │Continue│
│ Error  │    └────────┘
└───┬────┘
    │
    ▼
┌─────────────────────────────────────┐
│ ERROR TYPE IDENTIFICATION           │
└─────────────────────────────────────┘
    │
    ├─── API Connection Error
    │         │
    │         ▼
    │    ┌─────────────────────────┐
    │    │ catch (\Exception $e)   │
    │    │ Log error               │
    │    │ Return with message:    │
    │    │ "Payment initialization │
    │    │  failed"                │
    │    └─────────────────────────┘
    │
    ├─── Authentication Error
    │         │
    │         ▼
    │    ┌─────────────────────────┐
    │    │ Invalid API keys        │
    │    │ Return with message:    │
    │    │ "Invalid Moneroo        │
    │    │  credentials"           │
    │    └─────────────────────────┘
    │
    ├─── Validation Error
    │         │
    │         ▼
    │    ┌─────────────────────────┐
    │    │ Form validation failed  │
    │    │ Return with errors:     │
    │    │ - Required fields       │
    │    │ - Email format          │
    │    │ - Phone format          │
    │    └─────────────────────────┘
    │
    ├─── Session Error
    │         │
    │         ▼
    │    ┌─────────────────────────┐
    │    │ Transaction ID missing  │
    │    │ Redirect to:            │
    │    │ cancel page             │
    │    └─────────────────────────┘
    │
    ├─── Payment Verification Failed
    │         │
    │         ▼
    │    ┌─────────────────────────┐
    │    │ Status != success       │
    │    │ Redirect to:            │
    │    │ cancel page with message│
    │    └─────────────────────────┘
    │
    └─── Cart Empty (Products)
              │
              ▼
         ┌─────────────────────────┐
         │ No items in cart        │
         │ Return: 404             │
         └─────────────────────────┘
```

### Example Error Handling Code

```php
// TRY-CATCH PATTERN
try {
    $payment = Payment::init([...]);

    Session::put('monerooTransactionId', $payment['transaction_id']);

    return redirect()->away($payment['checkout_url']);

} catch (\Moneroo\Exceptions\UnauthorizedException $e) {
    // Specific handling for auth errors
    Log::error('Moneroo Auth Error: ' . $e->getMessage());
    return redirect()->back()
        ->with('unsuccess', 'Invalid Moneroo credentials. Please contact support.');

} catch (\Moneroo\Exceptions\InvalidPayloadException $e) {
    // Specific handling for payload errors
    Log::error('Moneroo Payload Error: ' . $e->getMessage());
    return redirect()->back()
        ->with('unsuccess', 'Invalid payment data. Please try again.');

} catch (\Exception $e) {
    // Generic error handling
    Log::error('Moneroo Error: ' . $e->getMessage());
    return redirect()->back()
        ->with('unsuccess', 'Payment initialization failed: ' . $e->getMessage());
}
```

---

## Webhook/Callback Process

### Callback Processing Flow

```
╔════════════════════════════════════════════════════════════════╗
║                  CALLBACK/NOTIFY PROCESSING                     ║
╚════════════════════════════════════════════════════════════════╝

┌──────────────────────┐
│ Moneroo Redirects    │
│ User Back            │
│ GET /*/moneroo/notify│
└──────┬───────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Controller::notify()                 │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║ STEP 1: RETRIEVE SESSION DATA                            ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ Get Transaction ID from Session      │
│ $txnId = Session::get(              │
│     'monerooTransactionId'           │
│ )                                    │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐     ┌────────────────┐
│ Transaction ID exists?               │─NO─→│ Redirect to    │
└──────┬───────────────────────────────┘     │ Cancel Page    │
       │ YES                                  └────────────────┘
       ▼
┌──────────────────────────────────────┐
│ Get Order Data from Session          │
│ (varies by module)                   │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║ STEP 2: VERIFY PAYMENT WITH MONEROO API                  ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ Call Moneroo API                     │
│ $verification = Payment::verify(     │
│     $transactionId                   │
│ )                                    │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ API Response:                        │
│ ┌────────────────────────────────┐   │
│ │ {                              │   │
│ │   "status": "success",         │   │
│ │   "id": "pay_xyz123",          │   │
│ │   "transaction_id": "txn_...", │   │
│ │   "amount": 99.99,             │   │
│ │   "currency": "USD",           │   │
│ │   "created_at": "2026...",     │   │
│ │   "completed_at": "2026..."    │   │
│ │ }                              │   │
│ └────────────────────────────────┘   │
└──────┬───────────────────────────────┘
       │
       ▼
╔═══════════════════════════════════════════════════════════╗
║ STEP 3: CHECK PAYMENT STATUS                             ║
╚═══════════════════════════════════════════════════════════╝
       │
       ▼
┌──────────────────────────────────────┐
│ Status === 'success' OR              │
│ Status === 'completed'?              │
└──────┬───────────────────────────────┘
       │
   ┌───┴───┐
   │       │
  YES     NO
   │       │
   ▼       ▼
┌──────────────────┐  ┌──────────────────┐
│ Process Success  │  │ Process Failure  │
└────┬─────────────┘  └────┬─────────────┘
     │                     │
     ▼                     ▼
╔═══════════════════╗  ┌──────────────────┐
║ SUCCESS PATH      ║  │ Clear Session    │
╚═══════════════════╝  │ Redirect Cancel  │
     │                 └──────────────────┘
     ▼
┌──────────────────────────────────────┐
│ Save Order/Purchase Record           │
│ - Create database record             │
│ - Set status = Completed             │
│ - Store transaction ID               │
│ - Store payment method = 'moneroo'   │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Generate Invoice PDF                 │
│ - Create from template               │
│ - Include order details              │
│ - Save to storage                    │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Send Confirmation Email              │
│ - To customer                        │
│ - With invoice attached              │
│ - Include order details              │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Clear Session Data                   │
│ Session::forget([                    │
│     'monerooTransactionId',          │
│     'courseData', // or orderData    │
│     'currency'                       │
│ ])                                   │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Redirect to Success Page             │
│ route('*.moneroo.complete')          │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ COMPLETED                             │
└───────────────────────────────────────┘
```

---

## Session Management

### Session Data Lifecycle

```
╔════════════════════════════════════════════════════════════════╗
║                  SESSION DATA LIFECYCLE                         ║
╚════════════════════════════════════════════════════════════════╝

PHASE 1: PAYMENT INITIALIZATION
─────────────────────────────────
┌──────────────────────────────────────┐
│ Session Data Stored:                 │
│ ┌────────────────────────────────┐   │
│ │ Course Module:                 │   │
│ │ - courseData (object)          │   │
│ │ - currency (string)            │   │
│ │ - monerooTransactionId (string)│   │
│ ├────────────────────────────────┤   │
│ │ Product Module:                │   │
│ │ - monerooOrderData (array)     │   │
│ │ - monerooTransactionId (string)│   │
│ │ - cart (array) [existing]      │   │
│ ├────────────────────────────────┤   │
│ │ Package Module:                │   │
│ │ - monerooPackageOrderId (int)  │   │
│ │ - monerooPackageId (int)       │   │
│ │ - monerooTransactionId (string)│   │
│ └────────────────────────────────┘   │
└───────────────────────────────────────┘

PHASE 2: USER AT MONEROO
─────────────────────────
┌──────────────────────────────────────┐
│ Session Persists                      │
│ (No changes)                         │
└───────────────────────────────────────┘

PHASE 3: CALLBACK/NOTIFY
──────────────────────────
┌──────────────────────────────────────┐
│ Session Data Retrieved:              │
│ $txnId = Session::get(...)           │
│ $data = Session::get(...)            │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Data Used for Verification           │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Order Processing...                  │
└──────┬───────────────────────────────┘
       │
       ▼

PHASE 4: COMPLETION
────────────────────
┌──────────────────────────────────────┐
│ Session Data Cleared:                │
│ Session::forget([                    │
│     'courseData',                    │
│     'currency',                      │
│     'monerooTransactionId',          │
│     'monerooOrderData',              │
│     'monerooPackageOrderId',         │
│     'monerooPackageId',              │
│     'cart', // for products          │
│     'coupon' // for products         │
│ ])                                   │
└───────────────────────────────────────┘

SESSION TIMEOUT HANDLING
─────────────────────────
┌──────────────────────────────────────┐
│ If Session Expires:                  │
│ ├─ notify() called                   │
│ ├─ Session::get() returns null       │
│ ├─ Redirect to cancel page           │
│ └─ Show appropriate error message    │
└───────────────────────────────────────┘
```

---

## Summary

This document provides comprehensive process diagrams and detailed workflows for:

✅ **Course Payment Process** - Complete enrollment lifecycle
✅ **Product Checkout Process** - Full e-commerce flow
✅ **Package Subscription Process** - Membership handling
✅ **Admin Configuration Process** - Gateway management
✅ **Error Handling Process** - Comprehensive error scenarios
✅ **Webhook/Callback Process** - Payment verification flow
✅ **Session Management** - Data lifecycle tracking

Each process includes:
- Step-by-step flow diagrams
- Code examples
- Data structures
- Error handling
- State transitions

---

**Documentation Date:** January 7, 2026
**Version:** 1.0.0
**Status:** Complete ✓
