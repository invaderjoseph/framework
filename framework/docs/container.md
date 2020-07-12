# Service Container

A Service Container (or dependency injection container) is simply a PHP object that manages the instantiation of services (i.e. objects).

*Ref: [Symfony 2.1 Book](https://symfony.com/doc/2.1/book/service_container.html)*

### Container Contract

The service container adheres to a standard DI contract i.e. an interface from PSR. This interface is specifically named ContainerInsterface, and has two methods that are required to exist in every class that implements the interface.

The methods are,
1. `get($id)` with `$id` variable required as a parameter. This retrieves a binding that is in the container that is identified by the `$id` variable.
1. `has($id)` also with `$id` variable as parameter to determine the existence of a binding in the container.

### Container Singleton Instance

The container needs to have a globally available singular instance of itself that is accessible throughout the entire application.

Two methods and a property are required to achieve this.
1. A method to set the globally available instance
2. A method to access the global container instance
3. Instance property to save the container in memory
