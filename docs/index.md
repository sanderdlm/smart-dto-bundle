# DataMapper bundle

A lot of people avoid using Doctrine entities directly inside Symfony forms, and for good reason. A popular alternative is using Data Transfer Objects or DTOs.

You can read the following Symfony Casts tutorial if this is news for you: https://symfonycasts.com/screencast/symfony-forms/form-dto

This approach adds an exra layer of security by never allowing your users to directly access your entities.

The (small) downside of using DTOs is that it adds extra boilerplate to your project. For each entity you make, you also have to make a DTO class.

I personally don't mind making the DTO classes themselves, because you usually add validation on these classes anyway.

What this bundle tries to solve though, is the hydration of DTOs from existing Doctrine entities.

When you want to update one of your existing entities, you'll have to create a new instance of your DTO, and map all the values from your entity to this new object. 

Usually this mapping is pretty straightforward:

```php
<?php

$updateItem = new ItemDataTransferObject(
    firstName: $existingEntity->getFirstName(),
    birthDate: $existingEntity->getBirthDate(),
    address: new AddressDataTransferObject(
        street: $existingEntity->getAddress()->getStreet()
    )
    // etc...
);

$form = $this->createForm(FormType::class, $updateItem);
```
With small forms and entities with mostly scalar values, this isn't too bad. But once you start building larger entities, with tons of properties, collections and relations, it can be very annoying to manually hydrate these DTOs. After all, in 95% of the properties, all we're doing is calling a simple getter and passing the value along.

This (tiny) bundle attempts to solve that problem, so you don't have to manually hydrate these objects and can get on writing other code.

See [the usage page](usage.md) to see how.