<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title>Lookuper</title>
    <link rel="stylesheet" href="assets/Lookuper/catego.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.6/Sortable.min.js" integrity="sha512-csIng5zcB+XpulRUa+ev1zKo7zRNGpEaVfNB9On1no9KYTEY/rLGAEEpvgdw6nim1WdTuihZY1eqZ31K7/fZjw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- script src="assets/Lookuper/catego_manager.js"></script -->
    <script src="assets/Lookuper/LookUpManager.js"></script>

</head>
<body>
<h1>Lookup</h1>

<script>
    var catego_value = {
        "categoria":"productCat",
        "label":"Uso",
        "label_plural":"Usos del Producto",
        "values": [
            {id:"2", label:"label #2","activo":"Activo"},
            {id:"3", label:"label #3", "activo":"Activo"},
            {id:"4", label:"label #4", "activo":"Activo"},
            {id:"5", label:"label #5", "activo":"Activo"},
            {id:"6", label:"label #6", "activo":"Activo"},
            {id:"7", label:"label #7", "activo":"Activo"},
            {id:"8", label:"label #8", "activo":"Activo"},
            {id:"9", label:"label #9", "activo":"Activo"},
            {id:"10", label:"label #10", "activo":"Activo"},

            {id:"12", label:"label #12","activo":"Activo"},
            {id:"13", label:"label #13", "activo":"Activo"},
            {id:"14", label:"label #14", "activo":"Activo"},
            {id:"15", label:"label #15", "activo":"Activo"},
            {id:"16", label:"label #16", "activo":"Activo"},
            {id:"17", label:"label #17", "activo":"Activo"},
            {id:"18", label:"label #18", "activo":"Activo"},
            {id:"19", label:"label #19", "activo":"Activo"},
            {id:"20", label:"label #20", "activo":"Activo"},
        ]
    }
   // var a = new Catego_manager("productCat", catego_value, '#taka', {reorder: true, add: true, edit:true, delete: true});
    var a = new LookUpManager("uso_producto", '#taka');
</script>

<select style="background-color: white" id="taka"></select> <button type="button" onclick="a.manage()"><i class="fa-regular fa-shelves"></i> Usos</button>
</body>
</html>