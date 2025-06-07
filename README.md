# E-Market Pharmacy

## Introduction
E-Market Pharmacy is a web and mobile application built using Laravel, following a client-server architecture. The platform allows patients to:

- Manage their prescriptions
- Make online pharmaceutical purchases
- Interact with pharmacies

It leverages JWT-based authentication for secure access control.

## Client-Server Architecture
The application follows a client-server architecture where:

- Frontend (Client): Handles user interface and interactions
- Backend (Server): Processes business logic and database operations
- RESTful API: Facilitates communication between client and server
  
![Client-Server Architecture](https://github.com/AASTUSoftwareEngineeringDepartment/E-Market-Pharmacy/blob/main/Backend/assets/client-server-architecture-design.png)

## Database Schema
The database schema is designed to efficiently manage users, prescriptions, orders, and pharmacy information.

![Database Schema](https://github.com/AASTUSoftwareEngineeringDepartment/E-Market-Pharmacy/blob/main/Backend/design/database/db.diagram.png)
## Features
- User Authentication: Secure login and registration using JWT
- Prescription Management: Upload and manage prescriptions
- Pharmacy Management: Admins can manage pharmacy details
- Order Management: Users can place and track orders
- Admin Dashboard: Manage products, users, and pharmacy info
- Secure Payment Integration: Payment gateway integration (PayPal and Chapa)
- Role-Based Access Control: Permissions for users and staff
- Chatbot Integration: Rag Based AI-based drug information service
- Email Notifications: Automated notifications for orders and payments

## Architecture
The application follows a client-server architecture with Laravel serving as the backend server.

### Server Structure
```
E-Market-Pharmacy/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── Auth/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── PasswordResetController.php
│   │   │   │   │   └── PasswordController.php
│   │   │   │   ├── Profile/
│   │   │   │   │   ├── AdminController.php
│   │   │   │   │   ├── CartController.php
│   │   │   │   │   ├── ChatController.php
│   │   │   │   │   ├── DrugController.php
│   │   │   │   │   ├── DrugLikeController.php
│   │   │   │   │   ├── EmailVerificationController.php
│   │   │   │   │   ├── InventoryLogController.php
│   │   │   │   │   ├── NotificationController.php
│   │   │   │   │   ├── OrderController.php
│   │   │   │   │   ├── PatientController.php
│   │   │   │   │   ├── PaymentController.php
│   │   │   │   │   ├── PharmacistController.php
│   │   │   │   │   ├── PlaceController.php
│   │   │   │   │   ├── PrescriptionController.php
│   │   │   │   │   └── ChatbotController.php
│   │   │   │   ├── ImageController.php
│   │   │   │   ├── GoogleAuthController.php
│   │   │   │   └── MessageController.php
│   │   │   └── Controller.php
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   │   ├── Cart.php
│   │   ├── Category.php
│   │   ├── ChatHistory.php
│   │   ├── ChatMessage.php
│   │   ├── Drug.php
│   │   ├── EmailVerificationToken.php
│   │   ├── InventoryLog.php
│   │   ├── Like.php
│   │   ├── Message.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── PasswordReset.php
│   │   ├── Payment.php
│   │   ├── Pharmacist.php
│   │   ├── Place.php
│   │   ├── Prescription.php
│   │   ├── User.php
│   ├── Notifications/
│   │   ├── EmailVerificationNotification.php
│   │   ├── LowStockAlertNotification.php
│   │   ├── NewOrderNotification.php
│   │   ├── NewPharmacistRegistration.php
│   │   ├── OrderCancelledNotification.php
│   │   ├── OrderCreatedNotification.php
│   │   ├── OrderDeliveredNotification.php
│   │   ├── OrderPaidNotification.php
│   │   ├── OrderReviewNotification.php
│   │   ├── OrderShippedNotification.php
│   │   ├── OrderStatusNotification.php
│   │   ├── OtpResetPassword.php
│   │   ├── PasswordResetNotification.php
│   │   ├── PaymentConfirmation.php
│   │   ├── PaymentConfirmationNotification.php
│   │   ├── PaymentReceivedNotification.php
│   │   ├── PharmacistOrderCancelledNotification.php
│   │   ├── PharmacistOrderDeliveredNotification.php
│   │   ├── PharmacistOrderPaidNotification.php
│   │   ├── PharmacistOrderShippedNotification.php
│   │   ├── PharmacistPaymentReceivedNotification.php
│   ├── Services/
│   │   ├── AdminEmailService.php
│   │   ├── ChatbotService.php
│   │   ├── CloudinaryService.php
│   │   ├── EmailVerificationService.php
│   │   ├── NotificationService.php
│   │   ├── OcrService.php
│   │   ├── PaymentNotificationService.php
│   │   ├── PrescriptionNotificationService.php
│   │   ├── UsernameGenerator.php
│   └── Providers/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
├── routes/
│   ├── api.php
│   ├── auth.php
│   ├── channels.php
│   ├── console.php
│   └── web.php
├── public/
└── storage/
```

```

## Authentication
The project uses JWT Authentication for secure user management, implemented with the help of the JWT Auth package for Laravel.

### How JWT Works
1. Login: Users authenticate with their credentials
2. JWT Token: Issued upon successful authentication
3. Authorization: Token is included in headers for subsequent requests

## Technologies Used
- Backend Framework: Laravel
- Database: MySQL
- Authentication: JWT (php-open-source-saver/jwt-auth package)
- Payment Gateways: PayPal 
- Chatbot Service: Custom Rag Based AI ML Trained
- Email Service: Laravel Mail

## Installation
### Prerequisites
- PHP (>=7.4)
- Composer
- MySQL

### Steps:
1. **Clone the repository**  
   ```
   git clone [https://github.com/AASTUSoftwareEngineeringDepartment/E-Market-Pharmacy.git]
   ```

2. **Navigate to the project folder**  
   ```
   cd Backend/Patient-management/my-app
   ```

3. **Install backend dependencies**  
   ```
   composer install
   ```

4. **Install JWT Auth package**  
   ```
   composer require php-open-source-saver/jwt-auth
   ```

5. **Setup environment**  
   ```
   cp .env.example .env
   ```

6. **Configure .env with DB and JWT details**

7. **Generate JWT secret**  
   ```
   php artisan jwt:secret
   ```

8. **Run database migrations**  
   ```
   php artisan migrate
   ```

9. **Serve the application**  
   ```
   php artisan serve
   ```

### Services Included
- **ChatbotService**: Rag Based ML Trained AI-powered drug information service
- **PaymentNotificationService**: Handles payment confirmations
- **UsernameGenerator**: Auto-generates unique usernames

### Contributing
1. Fork the repository
2. Create a feature branch  
   ```
   git checkout -b feature/your-feature
   ```
3. Make changes and commit  
   ```
   git commit -m "Add your message here"
   ```
4. Push to your fork  
   ```
   git push origin feature/your-feature
   ```
5. Create a pull request

🙏 **Acknowledgements**

- Laravel: PHP framework
- JWT Auth: Secure API authentication
- MySQL: Relational DBMS
- PayPal: Payment gateway integrations
- Custom ML Trained Chatbot: AI drug info support

# Deployment

- **Backend**: [https://e-pharmacybackend-production.up.railway.app](https://e-pharmacybackend-production.up.railway.app)  
- **Admin-side Frontend**: [https://e-market-pharmacy-admin.vercel.app](https://e-market-pharmacy-admin.vercel.app)  
- **Pharmacist-side Frontend**: [https://e-pharacy.vercel.app](https://e-pharacy.vercel.app)

📜 **License**  
This project is licensed under the MIT License.
```


