Я решил выполнить данное задание 2-мя методами:
1) Этот репозиторий
2) *github.com/vbudai/inline-test-mvc*. С помощью небольшого и самописного mvc фреймворка

База данных MySQL добавлена в репозиторий и называется **"inline-test.sql"**

PHP скрипт, который загружает посты и комментарии и выводит сообщение - **load_data.php**
Форма поиска записей по тексту комментария - **search_page.php**, который подключается в **index.php**

Данные выводятся в формате json. Также, учитывая, что у одной записи может быть несколько комментариев с искомой строкой, а также то, что таких записей может быть несколько, выводимые данные сгруппированы по постам, где к каждому посту прикреплены все его комментарии с искомой подстрокой.