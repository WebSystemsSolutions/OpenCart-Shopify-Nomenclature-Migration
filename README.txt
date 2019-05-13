__ENG__
Description - This module makes nomenclature (products, categories, manufacturers) migration from Shopify to OpenCart and vice versa.
1) Unzip the files into the root folder of the site.

2) Follow the link yourstore.com/system/migrations/new_param_category.php - to add the desired field in OpenCart, in the category table.

3) Go to Shopify shop, go to Apps on the left.
1. Next, "Manage private apps." At the top right is "Create a new private app".
2. Select the "Products, variants and collections", "Product information", "Discounts", "Inventory" permissions - Read and Write.
3. "Webhook API Version" - 2019-04 (Latest)
4. "Allow this app to access your storefront data using the Storefront API" - tick, and everything below.
5. Copy the "Admin API → Password".
(At https://shopify.github.io/themekit/ - there is a GIF, how to do it.)

4) Go to the Admin panel of the store on Opensart → Add-ons → Modules → Find Shopify <-> Opencart module and choose Install (Green plus). Then click edit. (Right from the plus).

5) Data Filling
1.Shopify Url (just domain) - enter the domain name of your store without http and www.
2. Enter Shopify Private App Password - the password you copied.
3. Click Save (top right).

6) Locate the module named "Shopify <-> Opencart" - (your store name) and click Edit.

It's all - you can use the module.

__Notes:__
1) For the correct download of images from Shopify store, PHP must have the root access to execute commands on Linux.
2) Downloading products from Shopify is much faster than downloading products to Shopify Store.
3) To stop downloading products into Shopify, just restart the page.
4) The module is developed for OpenCart version 2.1.0

__UA__
Переносить номенклатуру (продукти, категорії, виробники) з Shopify на OpenCart і навпаки.
1) Розархівуйте файли в кореневу папку сайту.

2) Перейдіть по посиланню yourstore.com/system/migrations/new_param_category.php - для додавання потрібного поля в OpenCart, в таблицю категорій.

3) Зайдіть в магазин Shopify, зліва перейдіть в Apps.
1. Далі «Manage private apps.». Справа вгорі «Create a new private app».
2. Оберіть права доступу "Products, variants and collections", "Product information", "Discounts", "Inventory" - Read and Write.
3. «Webhook API version» - 2019-04 (Latest)
4. «Allow this app to access your storefront data using the Storefront API» - поставте галочку, і все що нижче.
5. скопіюйте «Admin API → Password».
(На https://shopify.github.io/themekit/ - GIF, як це приблизно зробити.)

4) Перейдіть в Адмін панель магазину на Опенкарте → Додатки → Модулі → Знайдіть модуль «Shopify <-> Opencart» та оберіть "Встановити" (Зелений плюс). Потім натисніть редагувати. (Правіше від плюса).

5) Заповнення даних
1.Shopify Url (just domain) - введіть домменое ім'я вашого магазину без http і www.
2. Введіть Shopify Private App Password - той пароль що ви скопіювали.
3. Натисніть Зберегти (справа вгорі).

6) Знайдіть модуль і ім'ям «Shopify <-> Opencart» - (ім'я вашого магазину), і натисніть Редагувати.

Це все - можна користуватись модулем.

__Примітки:__
1) Для коректної завантаження картинок з Shopify магазину, PHP повинен мати права на виконання команд в Linux.
2) Вивантаження продуктів з Shopify набагато швидше, ніж завантажити продукти в Shopify магазин.
3) Що б зупинити завантаження продуктів в Shopify, просто перезавантажте сторінку.
4) Модуль розроблений для OpenCart version 2.1.0

__RU__
Описание - Модуль делает миграции номенклатуры (товаров, категорий, производителей) с Shopify в OpenCart и наоборот. 

1)Разархивируйте файлы в корневую папку сайта. 

2)Перейдите по ссылке yourstore.com/system/migrations/new_param_category.php - для добавления нужного поля в OpenCart, в таблицу категорий.

3)Зайдите в магазин Shopify, слева перейдите в Apps. 
1. Далее «Manage private apps.». Справа вверху «Create a new private app». 
2. Выбирите права доступа "Products, variants and collections", "Product information", "Discounts", "Inventory" - Read and Write. 
3. «Webhook API version» - 2019-04 (Latest)
4. «Allow this app to access your storefront data using the Storefront API» - поставте галочку, и все что ниже. 
5. Скопирейте «Admin API → Password».
(На https://shopify.github.io/themekit/ - есть GIF, как это примерно сделать.)

4)Перейдите в Админ панель магазина на Опенкарте → Дополнения → Модули → Найдите модуль «Shopify <-> Opencart» и выбираем Установить(Зеленый плюс). Потом нажмите редактировать. (правее от плюса).

5)Заполнение данных
1.Shopify Url(just domain) — введите домменое имя вашего магазина без http и www.
2. Введите Shopify Private App Password — тот пароль что вы скопировали.
3. Нажмите Сохранить(справа вверху).

6) Найдите модуль и именем «Shopify <-> Opencart» - (имя вашего магазина), и нажмите Редактировать.

Это вся настройка, модите пользоваться Модулем. 

__Примечания: __
1) Для коректной загрузки картинок с Shopify магазина, PHP должен иметь права на исполнение команд в Linux.
2) Выгрузка продуктов с Shopify намного быстрей, чем загрузить продукты в Шопифи магазин.
3) Что бы остановить загрузку продуктов в Shopify, просто перезагрузите страницу. 
4) Модуль разработан для OpenCart version 2.1.0
