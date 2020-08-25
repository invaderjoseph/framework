# Service Container

Service containers help standardize and centralize the way objects are constructed in an application. This design pattern emphasizes an architecture that promotes reusable and decoupled code.

## What is a Service

A service is any PHP object that performs some sort of “global” task. It’s a purposefully-generic name used in computer science to describe an object that’s created for a specific purpose (e.g. delivering emails). Each service is used throughout the application whenever a need for the specific functionality it provides arises.

As a rule, a PHP object is a service if it is used globally in your application.

## What is a Service Container?

A Service Container (or dependency injection container) is simply a PHP object that manages the instantiation of services (i.e. objects).
