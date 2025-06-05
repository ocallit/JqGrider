// noinspection JSUnusedGlobalSymbols

function Dater(options) {
    if (!(this instanceof Dater))
        return new Dater(options);

    const defaults = {
        null2String: "",
        shortDay: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
        longDay: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        shortMonth: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        longMonth: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
    }
    let settings = typeof options === 'object' && options !== null ? {...defaults, ...options} : {...defaults};

    /**
     * Formats a date according to the given format string in PHP style
     *
     * @param {Date|string|number|object|Array} inputDate - Date to format. Can be a Date object, a date string, or a timestamp.
     * @param {string} dateFormat="d/M/y" - The php format string. Defaults to "d/M/y"
     *      https://www.php.net/manual/en/datetime.format.php.
     * @returns {string|object|Array} - The formatted date string.
     * @throws {Error} - If an error occurs during the formatting process.
     */
    function date(inputDate, dateFormat = "d/M/y") {
        if("" === inputDate)
            return "";
        if(null === inputDate)
            return settings.null2String;

        if(Array.isArray(inputDate)) {
            for(let i = 0, len = inputDate.length; i < len; ++i)
                inputDate[i] = date(inputDate[i], dateFormat);
            return inputDate;
        }
        if(typeof inputDate === 'object' && !(inputDate instanceof Date)) {
            for(let i in inputDate)
                if(inputDate.hasOwnProperty(i))
                    inputDate[i] = date(inputDate[i], dateFormat);
            return inputDate;
        }

        try {
            function padZero(value) {
                return value < 10 ? `0${value}` : `${value}`;
            }

            let date;
            if(inputDate instanceof Date)
                date = inputDate;
            else if(typeof inputDate === "object")
                return "[object]";
            else if(isNaN(inputDate))
                date = is_ymd(inputDate) ?
                    new Date(`${inputDate}T00:00:00`) : new Date(inputDate);
            else
                date = new Date(inputDate);

            const parts = {
                d: padZero(date.getDate()),
                j: date.getDate(),
                D: settings.shortDay[date.getDay()],
                l: settings.longDay[date.getDay()],
                w: date.getDay(),

                m: padZero(date.getMonth() + 1),
                n: date.getMonth() + 1,
                M: settings.shortMonth[date.getMonth()],
                F: settings.longMonth[date.getMonth()],

                Y: date.getFullYear(),
                y: date.getFullYear().toString().slice(-2),

                H: padZero(date.getHours()),
                G: date.getHours(),
                h: padZero(date.getHours() > 12 ? date.getHours() - 12 : date.getHours()),
                g: date.getHours() > 12 ? date.getHours() - 12 : date.getHours(),
                i: padZero(date.getMinutes()),
                s: padZero(date.getSeconds()),

                a: date.getHours() < 12 ? "am" : "pm",
                A: date.getHours() < 12 ? "AM" : "PM",
            };

            let skip = false;
            let ret = [];
            for(let i = 0, len = dateFormat.length; i < len; ++i) {
                let c = dateFormat[i];
                if(c === "\\") {
                    skip = true;
                    continue;
                }
                if(skip) {
                    skip = false;
                    ret.push(c);
                    continue;
                }
                ret.push(parts.hasOwnProperty(c) ? parts[c] : c);
            }
            return ret.join("");
        } catch(error) {
            console.log("ERROR: formatIt.date:", error);
            console.log("       arguments", arguments);
            return inputDate;
        }

    }

    function ymd2Date(ymd) {
        if(is_ymd(ymd))
            return new Date(ymd + "T00:00:00")
        return new Date(ymd);
    }

    function is_ymd(value) {
        if(null === value || !isNaN(value) || typeof value !== "string" || value.length !== 10)
            return false;
        const regex = /^\d\d\d\d[-.\\\/_](0[1-9]|1[0-2])[-.\\\/_](0[1-9]|[1-2][0-9]|3[0|1])$/gm;
        return value.match(regex) !== null;
    }

    function is_ymd_time(value) {
        if(null === value || !isNaN(value) || typeof value !== "string" || value.length < 19 || value.length > 23)
            return false;
        const regex = /^\d\d\d\d[-.\\\/_](0[1-9]|1[0-2])[-.\\\/_](0[1-9]|[1-2][0-9]|3[0|1]).([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]/gm;
        return value.match(regex) !== null;
    }

    return {
        date: date,
        is_ymd: is_ymd,
        is_ymd_time: is_ymd_time
    };
}