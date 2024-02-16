```
      _      ___   ___        
     | |    / _ \ / _ \       
  ___| |__ | | | | | | |_ __  
 / __| '_ \| | | | | | | '_ \ 
 \__ \ | | | |_| | |_| | |_) |
 |___/_| |_|\___/ \___/| .__/ 
                       | |    
                       |_|    
```

<h1 align="center">Setup</h1>

This project uses <a href="https://devenv.sh/">devenv</a> to launch a developer environment ready to be used by everyone.  
This is even more consistent than a docker based setup.

First clone the repository  
```
https://github.com/rathmerdominik/sh00p
cd sh00p
```

### Devenv setup
First we need Nix, Cachix and devenv itself. This can be installed with the following commands  
```
sh <(curl -L https://nixos.org/nix/install) --daemon        # follow the steps here
nix-env -iA cachix -f https://cachix.org/api/v1/install
cachix use devenv
nix-env -if https://install.devenv.sh/latest
direnv allow .                                              # This will automatically start the devenv shell next time you visit the directory
devenv shell                                                # Only needed this one time
```
After that boot up the devenv shell by running this command **in a seperate terminal** in the sh00p directory  
`devenv up`


### Composer
Next we have to install the dependencies
```
composer install
cd tools/php-cs-fixer
composer install
cd ../..
```

### Database
The database is already provided by the devenv setup. To create the tables and execute the migrations execute the following commands
```
php bin/console doctrine:mi:mi
```
Want some product test data? I have written a command in `src/Command/GenerateTestDataCommand.php` that will generate some test data for you.  
``````
sh00p:generate-products 20 # will generate 20 products
``````

And thats it!  
Done! You can now access the API at `http://localhost:8000`!

<h1 align="center">Services</h1>

### Adminer
Adminer is a database management tool that is available at `http://localhost:9080`  

At the current state Adminer has <a href="https://github.com/vrana/adminer/pull/484">issues</a> with PHP 8.3.  
It will show you a nix path where the Adminer path is located.
You can fix the issue by running the following command
```
sudo sed -i 's/Trying to access array offset on value of type null/Trying to access array offset on( value of type)? null/g' <filename>
```

### Caddy

Caddy is a web server that hosts the Project at `http://localhost:8000`

### NelmioApiDoc

Want some API documentation? This is available at `http://localhost:8000/api/doc`  
This is also interactive! It allows you to test the API directly from the documentation.


<h1 align="center">Testing</h1>

There is a 100% coverage for all the services created for this project.  
To execute the existing tests run the following command
```
php bin/phpunit tests
```

<h1 align="center">Code style and Static Type checking</h1>

This project uses PHP-CS-Fixer for the code-style.
You can check and fix the code-style by issuing the following command  
```
php tools/php-cs-fixer/vendor/bin/php-cs-fixer fix -v
```

It also uses PHPStan for static type checking.  
To execute this run the following command
```
php vendor/bin/phpstan analyse
```

<hr>
<h1 align="center">Usage</h1>

Here is a little examples how a full workflow could look like.  
This example assumes that you have generated test products with the command ```sh00p:generate-products 20```

### Create a customer
`http://localhost:8000/api/v1/customers`  as a POST request
```json
{
  "name": "Dominik"
}
```
Creates a customer

Now can create a cart for the customer by issuing a POST request to `http://localhost:8000/api/v1/customers/1/carts`  
```json
{
  "name": "Test cart for all my super fancy tech stuff plus a typo here hehe"
}
```
I believe that a customer should have the possibility to create multiple carts, so they can share it to other people or just use it for themselves.

Now let's add a product to the cart by issuing a POST request to `http://localhost:8000//api/v1/customers/1/carts/1/products`  
```json
{
  "amount": 5,
  "product_id": 1
}
```

Oh whoops! Did you notice the typo in my cart name?  
Better fix it by issuing a PATCH request to `http://localhost:8000/api/v1/customers/1/carts/1`  
```json
{
  "name": "Test cart for all my super fancy tech stuff"
}
```

All done!  
Got myself some brandnew products!



