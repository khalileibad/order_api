# 📦 Order & Payment Management API (Laravel)

This project is a RESTful API designed to manage orders and payments, built with a strong focus on **SOLID principles**, **Clean Code**, and **Extensibility**. The system is architected to allow the addition of new payment gateways with minimal effort using the **Strategy Design Pattern**.

---

## 🛠 Features

### ADMIN
- [cite_start]**Manage Categories and Prodcuts**: Supports Admin user To Manage Categories and Prodcuts Data[cite: 8].

### 🛒 Order Management
- [cite_start]**Create Order**: Supports user details and nested purchased items with automatic total calculation[cite: 8].
- [cite_start]**Update Order**: Modify existing order information[cite: 9].
- [cite_start]**View Orders**: Retrieve all orders with support for pagination and filtering by status (`pending`, `confirmed`, `cancelled`)[cite: 11, 20].

### 💳 Payment Management
- [cite_start]**Process Payment**: Simulates payment processing (ID, Order ID, Status, Method)[cite: 12].

---

## 🏗 Design Patterns & Architecture

### Strategy Pattern (Extensibility)
[cite_start]The project implements a `PaymentStrategy` interface[cite: 28]. To add a new gateway:
1. Create a new class implementing `PaymentGatewayInterface`.
2. Implement the `process()` method.
3. [cite_start]Register the new gateway in the configuration/provider.
[cite_start]This ensures the system is **Open for extension but Closed for modification**[cite: 28, 39].

### Security & Validation
- [cite_start]**Authentication**: Secured via **JWT (JSON Web Token)** for registration and login endpoints[cite: 22, 23].
- [cite_start]**Validation**: All inputs are strictly validated with meaningful error messages[cite: 25, 26].

---

