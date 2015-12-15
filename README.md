# Бубсопедия

[Источник](http://www.boobpedia.com/)

# Разные страницы для примеров

Фильмография http://www.boobpedia.com/boobs/Olivia_(porn_star)
Фильмография 2 http://www.boobpedia.com/boobs/Fumie_Hosokawa
Фотогаллерея http://www.boobpedia.com/boobs/Lisa_Sparks

# Загребание страниц

Загуглил inurl:api site:boobpedia.com нашел [API](http://boobpedia.com/butler/api.php)

## 1. Список страниц с датами в JSON

http://boobpedia.com/butler/api.php?action=query&generator=allpages&gaplimit=500&gapfrom=%22&prop=info&format=json

## 2. Страница

http://boobpedia.com/butler/api.php?action=parse&page=Jackie%20Parker&format=json

# 3. Данные для парсинга

[Получение вики-разметки](http://www.boobpedia.com/butler/index.php?title=Jackie%20Parker&redirect=no&action=edit)

Содержательная часть: `$('textarea#wpTextbox1')`

```
<textarea id="wpTextbox1">
...
</textarea>
```


[Описание шаблонов](http://www.boobpedia.com/butler/index.php?title=Olivia&redirect=no&action=edit)

* [Описание шаблона Biobox](http://www.boobpedia.com/boobs/Template:Biobox_new)
* [Bra](http://www.boobpedia.com/boobs/Template:Bra)
* [Bra](http://www.boobpedia.com/boobs/Template:Bra_size)
* [Valid cup size](http://www.boobpedia.com/butler/index.php?title=Template:Valid_cup_size&action=edit)
* [Convert cup][http://www.boobpedia.com/butler/index.php?title=Template:Convert_cup&action=edit]

```
N|n|NO|no|No|None|none|Nope|nope|0=No
Y|y|YES|yes|Yes|Yeah|yeah|1=Yes
```

```
{{height|ft=5|in=6 }}
{{height|m=1.68}}

{{bra|34|D}}
{{weight|lb=119}}
{{weight|kg=68}}
measurements = 34D-25-36
```

# Картинки

* [Конкретная](http://boobpedia.com/butler/api.php?action=query&titles=File:Kerry_M_3_l_124.jpg&prop=imageinfo&iiprop=url)
* [Все](http://boobpedia.com/butler/api.php?action=query&list=allimages&ailimit=500&aifrom=%22&format=json)

### CRC32

```
printf('%u\n', crc32('boobpedia.comAlina_Puscau.jpg'));
```