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
 * Highlights the text inside a target element's class
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
