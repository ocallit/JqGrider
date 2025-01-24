
## Init

### HEAD
### HTML
```html
<input type="hidden" id="myIconInput" name="selected_icon" value="fa-regular fa-star\t#F00\t">
```

### Javascript
```javascript
$(document).ready(function() {
    $('#myIconInput').iconer_fontawsome({
        icons: {
            'iconer_fontawsome': [
                'fa-regular fa-star',
                'fa-solid fa-heart',
                'fa-solid fa-user',
                'fa-solid fa-home',
                'fa-solid fa-cog',
                'fa-solid fa-envelope',
                'fa-solid fa-bell',
                'fa-solid fa-calendar',
                'fa-solid fa-search',
                'fa-solid fa-trash'
            ]
        },
        buttonText: 'Icono'
    });
});
```

## Methods
### value
### extendIcons
### setIcons

## Value
* "fa-regular fa-user\t#color\t#background-color" example: "fa-regular fa-user\t#008000\t"
