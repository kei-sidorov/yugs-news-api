# Yugs Mobile News API

Yugs Mobile News API служит для взаимодействия мобильных приложений на базе Android и iOS с базой новостей порталов. Может использоваться как независимое приложение или как новостное прокси для вашей CMS.

Данный модуль предполагает, что все новости портала независимо от раздела имеют различный ID и вложенность новости не превышает единицу.

## Интеграция в готовую систему

### Подготовка класса
Для успешной интеграции в вашу систему необходимо создать класс в каталоге core/news (например core/news/MyNewsCollection.php)

Имя данного класса должно совпадать с названием файла без расширения .php (в нашем случае MyNewsCollection)

Класс должен имплементировать методы интерфейса NewsCollectionInterface:

```php
public function get($id);
```

Получает новость из хранилища по её ID $id. Отдаёт ассоциативный массив, который обязательно должен содержать значения по слудующим ключам:

* **id** - идентификатор новости
* **header** - заголовок новости
* **text** - полный текст новости
* **date** - отформатированная дата
* **original_link** - ссылка на новость на основном ресурсе

```php
public function getList($type, $limit, $page = 1);
```

Метод должен отдавать список выгружаемых новостей из хранилища по ID типа $type с заданным лимитом $limit и страницей $page (нумерация с единицы) в формате массива. Массив должен содержат ассоциативные массивы со следующими ключами:

* **id** - идентификатор новости
* **header** - заголовок новости
* **date** - отформатированная дата
* **image** - ссылка на главное изображение новости
* **original_link** - ссылка на новость на основном ресурсе
* **[lid]** - лид новости, необязателен (добавлен в версии 1.4 приложения)

Методы **add** и **delete** должны отдавать true. В случае интеграции в виде отдельного приложения необходимо реализовать эти методы самостоятельно, опираясь на описание параметров в файле интерфейса.

### Конфигурационный файл

Все настройки вынесенны в INI файл. Файл располагается по пути config/config.ini. В текущий момент директория содежит файл-пример. Внесите изменения и переименуйте файл в config.ini.

Для запуска приложения необходимо внести несколько исправлений.

1. В секции **[database]** поправить доступы к базе данных MySQL.
2. В секции **[news]** необходимо указать класс **class** созданного ранее класса коллекции новостей (в нашем случае MyNewsCollection)
3. В секции **[global]** указать вложенность **app-level** и путь до API **path** относительно Document Root. Если API будет развёрнут в %Document Root%/newsapi/ и будет доступен по адресу http://domain.ru/newsapi/ то значение **app-level** должно быть установленно в 2, а **path** в /newsapi/

### Подготовка шаблона
Если это необхоимо, внесите необходимые правки в php шаблон отображения новости newsTemplate-sample.php. Обязательно переименуйте в newsTemplate.php

### База данных

База данных должна содержать таблицу для хранения токенов push

```mysql
CREATE TABLE IF NOT EXISTS `tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `type` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
```

### Оповещение о новых новостях 

Если требуется оповестить пользователей о новой новости с помощью Push уведомлений, необходимо сделать запрос к API в виде POST запроса по URL
http://domain.ru/newsapi/news/add/ и передать слудующие параметры как multipart/form-data:

* type — ID типа новостей (рубрики); в случае интеграции в готовую систему не критично, можно указать 0
* header — заголовок новости
* text — текст новости; в случае интеграции в готовую систему не критично, можно указать любой текст
* images - список изображений в массиве JSON; в случае интеграции в готовую систему не критично, можно указать []
* notify - флаг отправки Push, если необходима отправка установить в 1

<a name="rubrics"></a>
### Рубрики новостей

Рубрики новостей загружаются и обновляются приложением в фоновом режиме при каждом запуске. Таким образом есть возможность добавить или исключить рубрику без обновления приложения. Спосок рубрик задается файлом **staticRubrics.php**, который должен в своем теле сформировать ассоциативный массив с названием **$rubrics**. Каждым элементом этого массива должен быть вложенный ассоциативный массив с обязательными и необязательными ключами.

Список ключей и их расшифровка:

* id — идентификатор раздела, будет передан при запросе списка новостей
* name — название раздела (должно быть коротким, иначе обрежется в приложении)
* [image] — полный URL до иконки (является обязательными, если приложение собрано с поддержкой иконок в меню)
* [viewTypePhone] — тип отображения списка новостей на телефоне (по умолчанию inApp)
* [viewTypePad] — тип отображения списка новостей на планшете (по умолчанию split)

Последние два ключа можно использовать для настройки вывода той или иной группы. Например, возможно одна из рубрик новостей предполагает содержит афишу и вывод постеров фильмов или мероприятий, и стандартный вывод с горизонтальной ориентацией изображения не подходит. Для этого нужно указать значение poster для обоих ключей, и приложение после обновления рубрик будет вводить спискок элементов в пригодном для этого виде.

Список доступных на данный момент видов перечислен ниже.

#### viewTypePhone

Определяет вид отображения на телефонах. Может содержать значения:

* inApp — состоит из ячеек малой и большой высоты, их комбинация настраивается при компляции приложения
* poster — вертикальная картика, три строчки текста
* bigCell - большие ячейки
* smallCell — малые ячейки

Ниже по порядку скписка представлены скриншоты этих видов 

<img src="https://github.com/kei-sidorov/yugs-news-api/raw/master/screenshots/phone-inApp.png" alt="inApp" width="200" height="360"> &nbsp;
<img src="https://github.com/kei-sidorov/yugs-news-api/raw/master/screenshots/phone-poster.png" alt="poster" width="200" height="360"> &nbsp;
<img src="https://github.com/kei-sidorov/yugs-news-api/raw/master/screenshots/phone-bigCells.png" alt="bigCell" width="200" height="360"> &nbsp;
<img src="https://github.com/kei-sidorov/yugs-news-api/raw/master/screenshots/phone-smallCells.png" alt="smallCell" width="200" height="360">

#### viewTypePad

Определяет вид отображения на планшетах. Может содержать значения:

* grid — горизонтальная картинка и заголовок, вывод в виде сетки по 2 элемента в строке 
* poster — вертикальная картика, заголовок и лид, вывод в виде списка, отцентрированный
* bigCell - большие ячейки с лидом новости, вывод в виде списка, отцентрированный
* split — экран разбит на два вида, список и новость

Ниже по порядку списка представлены скриншоты этих видов 

<img src="https://github.com/kei-sidorov/yugs-news-api/raw/master/screenshots/pad-grid.PNG" alt="grid" width="210" height="280"> &nbsp;
<img src="https://github.com/kei-sidorov/yugs-news-api/raw/master/screenshots/pad-poster.PNG" alt="poster" width="210" height="280"> &nbsp;
<img src="https://github.com/kei-sidorov/yugs-news-api/raw/master/screenshots/pad-bigCells.PNG" alt="bigCell" width="210" height="280"> &nbsp;
<img src="https://github.com/kei-sidorov/yugs-news-api/raw/master/screenshots/pad-split.PNG" alt="split" width="210" height="280">


### Баннера

Приложение при отображении экрана выбора новостей запрашивает баннера методом GET у скрипта banner.php, который додлжен быть расположен в корне с API. Реализацию баннерной системы интегратор должен реализовать самостоятельно. При запросе баннера приложение передаёт следующие параметры:

* devId - MD5 хеш от уникального идентификатора устройства, в основном используется для ограничений на показ и таргетинга
* device - тип устройства. Может принимать значения **phone** или **pad**
* type - тип баннера. Может принимать значения **bottom** или **popup**
* place - место показа. пока только одно значение **list**
* [listId] - числовой идентификатор текущей рубрики новостей, необязательный, передаётся когда place=list

**Ответ скрипта должен быть в формате *JSON* и иметь соответствующий заголовок Content-type.**

#### Popup баннера

Это всплывающие баннера поверх активного окна. Будьте внимательны при реализации: слишком частое их отображение будет раздражать пользователей приложения и привести к деинсталяции приложения и негативым отзывам в сторах.

При запросе popup баннера скрипт должен отдать пустой ответ если баннер не нужно показывать и передать параметры баннера если баннер нужно показывать. Список параметров для всплывающего баннера:
 
* type — тип баннера, для popup баннера единственное верное значение это popup
* image - картинка баннера. 
* destination - ссылка, которая откроется по нажатию
* duration - время в секундах, в течении которого баннер нельзя закрыть
* interval - время в секундах до следующего запроса баннера
* [width] - ширина баннера в писксель-независимых точках
* [height] - высота баннера в писель-независимых точках

Картинка баннера должна быть в формате PNG, JPG или неанимированный GIF. Стоит принять во внимание, что плотность пискселей на современных мобильных устройствах выше, чем 1 писксель на точку, выбирайте разрешение картинки исходя из этого факта.

Ссылка на рекламируемую страницу, указанная в параметре destination по умолчанию открывается c помощью встроенного в приложение браузера. Если нужно открыть ссылку во внешем системном браузере — укажите **#external** в конце ссылки.

Интервал времени в секундах до следующего запроса баннера имеет минимальное значение, раньше которого не произойдет слудующий запрос. Как писалось ранее, баннера запрашиваются при запросе списка новостей, при условии, что прошло времени не меньше, чем было указанно в предыдущем запросе баннера.
 
Значения высоты и ширины являются независмыми от физического разрешения экрана, и указаны в точках. Параметры не обязательны и по умолчанию имеют значение 250&times;400 точек. Используя эти параметры вы можете показывать разные баннера для планшетов и телефонов. Если значение высоты или ширины в ответе скрипта окажутся больше, чем таковые у устройства, баннер будет проигнорирован приложением.

#### Bottom баннера

Это баннера в нижней части экрана. Могут быть двух вариантов: HTML5 баннера или баннер из системы Google AdMob. Их возможно чередовать, например, когда рекламное место не продано — показывать AdMob, в ином случае собсвенные баннера.

При запросе popup баннера скрипт должен отдать пустой ответ если баннер не нужно показывать и передать параметры баннера если баннер нужно показывать. В зависимости от баннера список параметров разный:

##### Google AdMob

* type - тип баннера, должен быть равен admob
* admob_id - идентификатор в системе google AdMob

##### HTML

* type - тип баннера, должен быть равен html
* url - ссылка до html файла с баннером
* rotation — флаг ротации баннера, булево значение
* [duration] — время показа баннера в секундах, обязательный, если rotation=true

Если задан флаг ротации баннера, приложение будет показывать баннер в течении duration секунд, после чего сделает повторный запрос.
 
При использовании HTML баннера нужно реализовать минимальные требования к баннеру. При запросе приложение дописывает к url значение размера баннера, которое зависит от размера экрана устройства в формате #[ширина]x[высота]. Высота зависит от ширины устройства:

* 32 точки, если ширина экрана менее 320 точек
* 50 точек, если ширина >= 320, но меньше 700
* 90 точек, если ширина >= 700 точек

С помощью параметра разработчик баннера должен организвать адаптивность и не допустить скролл.

**Внимание** Приложение не покажет баннер, пока не будет инициироаван переход по ссылке banner:ready (window.location = 'banner:ready'). Желательно инициировать этот переход через Javascript при полной загрузке ресурсов веб-страницы баннера, на событии window load.

В баннере запрещены ссылки с внешними ссылками, вместо этого использвется механизм получения ссылки из javascript. В классическом случае ссылка должна ввести на url banner:navigate (a href="banner:navigate"), приложение отловит этот переход и попытается получить скрипт через метод getNavigateURL(). Этот метод должен быть в глобальной видимости у страницы.

Ссылка на рекламируемую страницу, которая отдаётся методом getNavigateURL, по умолчанию открывается c помощью встроенного в приложение браузера. Если нужно открыть ссылку во внешем системном браузере — укажите **#external** в конце ссылки.
   
Реализацию простейшего баннера можете подсмототреть в тестовом файле [banners/sample.html](banners/sample.html)