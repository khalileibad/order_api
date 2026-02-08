# 📦 Order & Payment Management API (Laravel)

This project is a RESTful API designed to manage orders and payments, built with a strong focus on **SOLID principles**, **Clean Code**, and **Extensibility**. The system is architected to allow the addition of new payment gateways with minimal effort using the **Strategy Design Pattern**.

---

## 🛠 Features

### ADMIN
- **Manage Categories and Prodcuts**: Supports Admin user To Manage Categories and Prodcuts Data.

### 🛒 Order Management
- **Create Order**: Supports user details and nested purchased items with automatic total calculation.
- **Update Order**: Modify existing order information.
- **View Orders**: Retrieve all orders with support for pagination and filtering by status (`pending`, `confirmed`, `cancelled`).

### 💳 Payment Management
- **Process Payment**: Simulates payment processing (ID, Order ID, Status, Method).

---

## 🏗 Design Patterns & Architecture

### Strategy Pattern (Extensibility)
The project implements a `PaymentStrategy` interface. To add a new gateway:
1. Create a new class implementing `PaymentGatewayInterface`.
2. Implement the `process()` method.
3. Register the new gateway in the configuration/provider.
This ensures the system is **Open for extension but Closed for modification**.

### Security & Validation
- **Authentication**: Secured via **JWT (JSON Web Token)** for registration and login endpoints.
- **Validation**: All inputs are strictly validated with meaningful error messages.

---

#### 📂 API Documentation (Postman)
The repository includes a Postman collection file (`Order API.postman_collection.json`) to assist in exploring the API.
- **Purpose**: Provides a clear map of all endpoints, required variables, and expected request bodies.
- **Examples**: Includes templates for Success and Error scenarios to guide integration.