
function Formatter(){
    const spanned = false;
    const predefinedFormats = {
        date: {formatter:"date", format: "d/M/y"},
        short_date: {formatter:"date", format: "d/M/y"},
        "short date": {formatter:"date", format: "d/M/y"},
        dateTime: {formatter:"date", format: "d/M/y H:ii"},
        ymd: {formatter:"date", format: "yyyy-mm-dd"},
        number: {formatter:"number", format: "#,##0.00"},
        float: {formatter:"number", format: "#,##0.00"},
        double: {formatter:"number", format: "#,##0.00"},
        decimal: {formatter:"number", format: "#,##0.00"},
        int: {formatter:"number", format: "#,##0"},
        bool: {format: {true:"Si", false:"No"}},
        "bit": {format: {true:"Si", false:"No"}},
    }
    const fieldNames = {
        created:{formatter:"date", format:""}
    }

    function predinedFormatsInit() {
        const ints =["tinyint", "smallint", "mediumint", "int", "bigint", "integer",
            "tinyint unsigned", "smallint unsigned", "mediumint unsigned", "int unsigned", "bigint unsigned"];
        for(let i = 0, len = ints.length; i < len; ++i)
            predefinedFormats[ints[i]] = {formatter:"number", format: "#,##0"};
    }
    predinedFormatsInit();

    function field(fieldName, value) {
        if(fieldNames.hasOwnProperty(fieldName)) {
            let format = fieldNames[fieldName];
            if(format.hasOwnProperty("formatter")) {
                if(format.hasOwnProperty("format"))
                    return format.formatter(value, format.format);
                else
                    return format.formatter(value);
            }
        }
        if(predefinedFormats.hasOwnProperty(fieldName)) {
            let format = predefinedFormats[fieldName];
            if(format.hasOwnProperty("formatter")) {
                if(format.hasOwnProperty("format"))
                    return format.formatter(value, format.format);
                else
                    return format.formatter(value);
            }
        }
        return deduce(value);
    }

    function deduce(value) {
        if(typeof value === "boolean")
            return bool(value);
        if(!isNaN(value))
            return number(value);
        if(is_ymd(value) || is_ymd_time(value) || value instanceof Date)
            return date(value);
        // email
        // tel
        // link
        // hashtag
        return value;
    }

    function date(value, dateFormat = "d/M/y") {}
    function number(value, format = "#,##0.00") {}
    function bool(value, format ={true:"Si", false:"No"}) {}
    function title(value) {}
    function label(value) {}
    function email(value) {}

}