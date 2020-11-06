/**
 * Highlights a string inside an element given its class. (expects a style class of .highlight)
 * Does not work well with single characters so, it must be a search value greater or equal to 2.
 * @param {string} str string to search in element
 * @param {string} targetClassName element's class to search in
 * @param {boolean} [capitalize=false] weather to capitalize the matched string
 */
function highlight(str, targetClassName, capitalize=false) {
    if(str.length >= 2){
        $(`.${targetClassName}`).each(function(){
            let cap = '';
            if (capitalize === true) cap = 'style="text-transform: capitalize;"';
            let search_regexp = new RegExp(str, "gi");
            $(this).html(
                $(this).html().replace(
                    search_regexp,`<span class='highlight' ${cap}>${str}</span>`
                )
            );
        });
    }
}

/**
 * Highlights the text inside a target element's class.
 * @param {string} str text to highlight
 * @param {string} target target element class
 * @param {boolean} split weather to split the word by white space
 * @param {boolean} capitalize weather to capitalize the matched word
 */
function highlightHelper(str, target, split=false, capitalize=false) {
    let arr = [str];
    if (split)
        arr = str.split(' ');
    for (let i = 0; i < arr.length; i++)
        highlight(arr[i], target, capitalize)
}

/**
 * Highlight the words in the element with the title class
 * @param {string} str text to highlight
 * @param {boolean} split weather to split the word by white space
 * @param {boolean} capitalize weather to capitalize the matched word
 */
function highlightTitles(str, split=true, capitalize=true) {
    highlightHelper(str, 'title', split, capitalize);
}

/**
 * Highlight the words in the element with the description class
 * @param {string} str text to highlight
 * @param {boolean} split weather to split the word by white space
 * @param {boolean} capitalize weather to capitalize the matched word
 */
function highlightDescriptions(str, split=true, capitalize=false) {
    highlightHelper(str, 'description', split, capitalize);
}

/**
 * Highlight the words in the element with the file class
 * @param {string} str text to highlight
 * @param {boolean} split weather to split the word by white space
 * @param {boolean} capitalize weather to capitalize the matched word
 */
function highlightFiles(str, split=true, capitalize=false) {
    highlightHelper(str, 'file', split, capitalize);
}

/**
 * Highlight the words in the element with the type, title, author, subject, description, and file class
 * @param {string} str text to highlight
 * @param {boolean} split weather to split the word by white space
 * @param {boolean} capitalize weather to capitalize the matched word
 */
function highlightAll(str, split=true, capitalize=false) {
    highlightHelper(str, 'type', split, capitalize);
    highlightTitles(str, split);
    highlightHelper(str, 'author', split, capitalize);
    highlightHelper(str, 'subject', split, capitalize);
    highlightDescriptions(str, split);
    highlightFiles(str, split);
}

/**
 * Returns the icon related to the input.
 * @param {string} str name of the icon.
 * @returns {string} returns the html for the icon.
 */
function getIcon(str) {
    switch (str.toLowerCase()) {
        case "libro":
        case "book":
            return '<i class="fas fa-book"></i>';

        case "novel":
        case "novela":
            return '<i class="fas fa-book-reader"></i>';

        case "arte":
        case "art":
            return '<i class="fas fa-paint-brush"></i>';

        case "foto":
        case "photo":
        case "picture":
            return '<i class="far fa-image"></i>';

        case "periódico":
        case "periodico":
        case "newspaper":
            return '<i class="far fa-newspaper"></i>';

        case "revista":
        case "magazine":
            return '<i class="fas fa-book-open"></i>';

        case "document":
        case "documento":
            return '<i class="fas fa-file-invoice"></i>';

        case "word":
        case "word document":
        case "doc":
        case "docx":
            return '<i class="far fa-file-word"></i>';

        case "ppt":
        case "pptx":
        case "powerpoint":
        case "powerpoint presentation":
            return '<i class="far fa-file-powerpoint"></i>';

        case "excel":
        case "xlsx":
        case "xls":
        case "excel spreadsheet":
            return '<i class="far fa-file-excel"></i>';

        case "csv":
        case "comma-separated values":
        case "comma separated values":
            return '<i class="fas fa-file-csv"></i>';

        case "pdf":
            return '<i class="fas fa-file-pdf"></i>';

        case "zip":
        case "archive":
            return '<i class="fas fa-file-archive"></i>';

        case "code":
        case "programming":
            return '<i class="fas fa-file-code"></i>';

        case "video":
        case "movie":
        case "animation":
            return '<i class="far fa-file-video"></i>';

        case "audio":
        case "song":
        case "music":
            return '<i class="far fa-file-audio"></i>';

        case "media":
            return '<i class="fas fa-photo-video"></i>';

        case "atlas":
        case "map":
            return '<i class="fas fa-atlas"></i>';

        case "bible":
            return '<i class="fas fa-bible"></i>';

        case "quran":
            return '<i class="fas fa-quran"></i>';

        case "torah":
            return '<i class="fas fa-torah"></i>';

        default:
            return '<i class="far fa-file-alt"></i>';
    }
}