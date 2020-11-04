PDFJS.disableWorker = true;
let allowReload = false;
const loadingImg = document.getElementById('overlay');

/*initiate the autocomplete function on the "type" element, and pass along the types array as possible autocomplete values:*/
autocomplete(document.getElementById("type"), types);

/* Show files start */
let counter = 0;
let fileInfo = document.getElementById('file-info');
let filesInput = document.getElementById('files');
let fileList = document.getElementById('fileList');
const emptyFilesInput = filesInput.cloneNode(true);

const showImage = document.getElementById('show');
const inputImageShow = document.getElementById('image');
const customImage = document.getElementById('customImage');
const canvasImageShow = document.getElementById('the-canvas');
const fileInputForThumbnail = document.getElementById('selectedFileInput');
const pageNumberInput = document.getElementById('pageNumber');
let imageSet = false;

const descriptionTextAreaField = document.getElementById('description');
const descriptionHelp = document.getElementById('descriptionHelp');
const metadataTextAreaField = document.getElementById('metadata');
const metadataHelp = document.getElementById('metaHelp');
const helpText = `Presione control y enter (<code>CTRL+ENTER</code>) para una nueva línea donde esta el cursor`;
const helpText2 = `Presione <code>CONTROL</code> y enter (<code>CTRL+ENTER</code>) para una nueva línea donde esta el cursor`;
const helpText3 = `Presione <code>CONTROL</code> y <code>ENTER</code>) (<code>CTRL+ENTER</code>) para una nueva línea donde esta el cursor`;

addNewLineCapability(descriptionTextAreaField);
addNewLineCapability(metadataTextAreaField);

/**
 * Adds new line capability to textarea, by hitting ctrl+enter.
 * @param {HTMLElement} textarea the textarea to add new line capability
 */
function addNewLineCapability(textarea) {
    textarea.addEventListener('keydown', function(e) {
        const isDes = textarea.id === 'description';
        const isMeta = textarea.id === 'metadata';
        if (e.ctrlKey) {
            if (isDes) {
                descriptionHelp.innerHTML = helpText2;
            } else if (isMeta) {
                metadataHelp.innerHTML = helpText2;
            }
        }

        if(e.ctrlKey && e.keyCode === 13) {
            if (isDes) {
                descriptionHelp.innerHTML = helpText3;
            } else if (isMeta) {
                metadataHelp.innerHTML = helpText3;
            }

            const position = this.selectionEnd;
            this.value = this.value.substring(0, position) + '\n' + this.value.substring(position);
            this.focus();
            this.selectionEnd = position + 1; // moves cursor to newline
        }
    });

    textarea.addEventListener('keyup', function(e) {
        descriptionHelp.innerHTML = helpText;
        metadataHelp.innerHTML = helpText;
    });
}

/**
 * Creates a FileList object for the dynamic uploading files system.
 * @param items File objects
 * @returns {FileList} FileList object
 * @private (not meant for general use)
 */
function _FileList(items) {
    // flatten rest parameter
    items = [].concat.apply([], [items]);
    // check if every element of array is an instance of `File`
    if (items.length && !items.every(function(file) {
        return file instanceof File
    })) {
        throw new TypeError("expected argument to FileList is File or array of File objects");
    }
    // use `ClipboardEvent("").clipboardData` for Firefox, which returns `null` at Chromium
    // we just need the `DataTransfer` instance referenced by `.clipboardData`
    let dt = new ClipboardEvent("").clipboardData || new DataTransfer();
    // add `File` objects to `DataTransfer` `.items`
    for (let i = 0; i < items.length; i++) {
        dt.items.add(items[i])
    }
    return dt.files;
}

/**
 * Turns on overlay on body
 */
function on() {
    loadingImg.style.display = 'block';
}

/**
 * Turns off overlay on body
 */
function off() {
    loadingImg.style.display = 'none';
}
let data;
function insertCustomImage(event) {
    if (event.files && event.files[0]) {
        let reader = new FileReader();
        reader.onload = function (e) {
          showImage.src = e.target.result;
          inputImageShow.value = showImage.src;
          imageSet = true;

        }
        reader.readAsDataURL(event.files[0]);
    }


}

filesInput.onchange = function(e) {
    for (let i = 0; i < e.target.files.length; i++) {
        let li = document.createElement("li");
        li.className = "list-group-item d-flex justify-content-between align-items-center";
        let input = document.createElement("input");
        input.name = `file-${++counter}`;
        input.id = `file-${++counter}`;
        input.type = "file";
        input.files = new _FileList(e.target.files[i]);
        input.className = "form-control btn hide-overflow";
        li.appendChild(input);
        li.appendChild(document.createElement("span"));
        fileList.appendChild(li);
    }

    updateFilesData();

    if (!imageSet) updateImage();
}

/**
 * Clears everything related to the image.
 * @param {boolean} [clear=true] if true clears the image data else does nothing.
 */
function clearImage(clear=true) {
    if (clear === true) {
        showImage.src = "images/pdf-placeholder.jpg";
        inputImageShow.value = "";
        imageSet = false;
    }
}

function changeImage(fileId, filePage, resetPageNumber=false) {
    if (resetPageNumber) pageNumberInput.value = 1;
    updateImage(document.getElementById(fileId), 0, parseInt(filePage));
}

function generateFileInputForImage() {

    //clears fileInputForThumbnail input
    fileInputForThumbnail.innerHTML = '';

    // generates new view
    let element, name, fileId;
    for (let i = 1; i <= counter; i++) {
        try {
            fileId = `file-${i}`;
            element = document.getElementById(fileId);
            name = element.files[0].name;
            console.log(element.files[0])
            if (name.endsWith('.pdf')) {
                fileInputForThumbnail.innerHTML += `<option value='${fileId}'>${name}</option>`
            }
        } catch (ignored) {}
    }

    pageNumberInput.value = 1;
}

/**
 * <p>Updates the image.
 * <ul>
 * <li>Needs a canvas HTMLElement (canvasImageShow) to generate the thumbnail and get the dataUrl</li>
 * <li>Needs a img HTMLElement (showImage) to show the base64 image</li>
 * <li>Needs a input HTMLElement (inputImageShow) to save the base64 data value (in the input's value) for POST</li>
 * </ul>
 * @param {HTMLElement} [id=filesInput] ID of the file html element
 * @param {number} [fileIdx=0] the index of the file in the element
 * @param {number} [pageNumber=1] the page number for the image
 */
function updateImage(id = filesInput, fileIdx = 0, pageNumber=1) {
    file = id.files[fileIdx];
    if (file) {
        fileReader = new FileReader();
        fileReader.onload = function(ev) {
            PDFJS.getDocument(fileReader.result).then(function getPdfHelloWorld(pdf) {
                pdf.getPage(pageNumber).then(function getPageHelloWorld(page) {
                    const scale = 1.5;
                    const viewport = page.getViewport(scale);
                    const context = canvasImageShow.getContext('2d');
                    canvasImageShow.height = viewport.height;
                    canvasImageShow.width = viewport.width;
                    const task = page.render({canvasContext: context, viewport: viewport});
                    task.promise.then(function(){
                        clearImage();
                        showImage.src = canvasImageShow.toDataURL('image/jpeg');
                        inputImageShow.value = showImage.src;
                        imageSet = true;

                    });
                });
            }, function(error) {
                console.log(error);
                clearImage();
                updateImage(id, ++fileIdx, pageNumber); //recurse to next file if it fails (i.e., not a valid PDF structure)
            });

        };
        fileReader.readAsArrayBuffer(file);
    }
}


/**
 * Updates the listView (ul element) in the interface, and other data related to the files.
 * @param {boolean} [updateListView=true] Whether to handle the updating of the files data.
 */
function updateFilesData(updateListView = true) {
    counter = fileList.children.length;
    document.getElementById('number-of-files').value = counter;

    const INPUT = "INPUT";
    const SPAN = "SPAN";
    let fileListChildren = fileList.children;
    let grandChildren, grandChild;
    for (let i = 0; i < fileListChildren.length; i++) {
        grandChildren = fileListChildren[i].children;
        for (let j = 0; j < grandChildren.length; j++) {
            grandChild = grandChildren[j];
            switch (grandChild.tagName) {
                case INPUT:
                    grandChild.name = `file-${i+1}`;
                    grandChild.id = `file-${i+1}`;
                    console.log(grandChild.name);
                    break;
                case SPAN:
                    grandChild.innerHTML =
                        `<div class="input-group-append">
                            <button class="btn btn-outline-danger" type="button" onclick="remove(${i})" title="Eliminar el archivo #${i + 1} (${formatSpecificFileSize('file-' + (i+1))})">
                            <i class="far fa-trash-alt"></i>
                            </button>
                            </div>`;
                    break;
            }

            handleListView(updateListView);
        }
    }
}

/**
 * Removes a list item in the listView element (ul element)
 * @param {number} idx The index location of the list item in the ul element.
 */
function remove(idx) {
    let fileListChildren = fileList.children;
    if (idx >= 0 && idx <= fileListChildren.length) {
        fileListChildren[idx].parentNode.removeChild(fileListChildren[idx]);
        updateFilesData(false);
        handleListView()
    } else {
        console.log(idx + ' does not exist in fileList...');
    }
}

/**
 * Updates the list view and image.
 * @param {boolean} [handle=true] weather to handle the view (i.e., execute the code)
 */
function handleListView(handle = true) {
    if (handle === true) {

        const len = fileList.children.length;
        if (len === 0) {
            fileInfo.innerHTML = "";
            filesInput.files = emptyFilesInput.files;
            clearImage();
        } else {
            let s = "";
            if (len > 1) s = "s";
            fileInfo.innerHTML = `${s === "" ? 'El' : 'Los'} Archivo${s} Selccionado${s} (${len} archivo${s} - ${totalFileSize()}):`;
            let warnDiv = document.getElementById('size-warning');
            clearImage();

            let element = undefined;
            for (let i = 1; i <= counter; i++) {
                element = document.getElementById(`file-${i}`);
                try {
                    if (element.files[0].name.endsWith('.pdf')) {
                        updateImage(element);
                        break;
                    }
                } catch (e) {
                    clearImage();
                }
            }

            if (size > 4e+7) {
                warnDiv.innerHTML = `<?php echo showWarn('Precaución:', 'Se ha pasado del tamaño máximo total (40MB). Vea el hint de seleccionar los archivos. Puede subir un archivo primero y luego editar el articulo para añadir otro documento.') ?>`;
            } else warnDiv.innerHTML = "";
        }

        generateFileInputForImage();
    }
}

/**
 * Formats the bytes as (e.g., KB, MB, KB, etc) depending on how large the number is.
 * @param {number} bytes The bytes (file size)
 * @param {number} [decimals=2] number of decimal places
 * @returns {string} returns the formatted size of the bytes
 */
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

/**
 * Formats the size of a file input element.
 * @param {string} id The id of the file input element
 * @returns {string} Returns the formatted size of the File
 */
function formatSpecificFileSize(id) {
    return formatBytes(getFileSize(id));
}


let size = 0;

/**
 * Calculates the total size of the file inputs (which match id of 'file-[number]')
 * @returns {string} Returns the formatted total size of the files.
 */
function totalFileSize() {
    if (counter > 0) {
        size = 0;
        for (let i = 0; i < counter; i++) {
            size += getFileSize(`file-${i + 1}`);
        }
        return formatBytes(size);
    }
}

/**
 * Returns the size of the file in a file input element.
 * @param {string} id The id of the file input element
 * @returns {number} returns the size of the File
 */
function getFileSize(id) {
    let fileInput = document.getElementById(id);
    let size = -1;
    if (fileInput) {
        try {
            size = fileInput.files[0].size; /* Size returned in bytes. */
        } catch (e) {
            size = new ActiveXObject("Scripting.FileSystemObject").getFile(fileInput.value).size;
        }
    }

    return size;
}
/* Show files end */

/**
 * Erases the last value inserted into the readonly input.
 * @param {string} input The input id.
 * @param {string} output The input readonly id.
 */
function deleteLastReadonly(input, output) {
    const field = document.getElementById(input);
    const readonly = document.getElementById(output);
    if (readonly.value.trim() === "") return;
    let arr = readonly.value.trim().split(",");
    let popped = arr.pop().trim();
    readonly.value = (arr + "").trim();
    if (field.value === "") {
        field.value = popped;
    } else {
        field.value += `, ${popped}`;
    }
}

/**
 * Erases the readonly input.
 * @param {string} input The input id.
 * @param {string} output The input readonly id.
 */
function deleteReadonly(input, output) {
    const values = document.getElementById(output).value;
    document.getElementById(output).value = "";
    const field = document.getElementById(input);
    if (field.value === "") {
        field.value = values.trim();
    } else {
        field.value += `, ${values.trim()}`;
    }
}

/**
 * Adds the csv from the input element to the output element.
 * @param {string} input The input id.
 * @param {string} output The input readonly id.
 */
function addAllToReadonly(input, output) {
    const field = document.getElementById(input);
    const readonly = document.getElementById(output);
    let fieldVal = field.value.trim();

    if (fieldVal === "") {
        field.value = "";
        return;
    }

    let arr = fieldVal.split(",");

    for (let i = 0; i < arr.length; i++) {
        if (readonly.value === "") {
            readonly.value += `${arr[i].trim()}`
        } else {
            readonly.value += `, ${arr[i].trim()}`
        }
    }

    readonly.value = titleCase(readonly.value.trim());
    readonly.title = readonly.value;
    field.value = "";

    function titleCase(str) {
        let splitStr = str.toLowerCase().split(' ');
        for (let i = 0; i < splitStr.length; i++)
            splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);

        return splitStr.join(' ');
    }
}

/**
 * Adds the text from the input element to the output element.
 * @param {string} input The input id.
 * @param {string} output The input readonly id.
 */
function addToReadonly(input, output) {
    const field = document.getElementById(input);
    const readonly = document.getElementById(output);
    let fieldVal = field.value.trim();
    if (fieldVal === "") {
        field.value = "";
        return;
    } else if (fieldVal.includes(',')) {
        field.value = field.value.replaceAll(",", "");
        return;
    }

    if (readonly.value === "") {
        readonly.value += `${fieldVal.trim()}`
    } else {
        readonly.value += `, ${fieldVal.trim()}`
    }

    readonly.value = titleCase(readonly.value.trim());
    readonly.title = readonly.value.trim();
    field.value = "";

    function titleCase(str) {
        let splitStr = str.toLowerCase().split(' ');
        for (let i = 0; i < splitStr.length; i++)
            splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);

        return splitStr.join(' ');
    }
}

/**
 * Changes the label of the calendar to Year only or Date only (when the checkbox is clicked).
 * @param {string} id The id string of the element
 */
function changePubDateToYear(id) {
    const label = document.getElementById(id);
    const input = document.getElementById('yearOnly');
    const from = 'Fecha';
    const to = 'Año';

    if (input.checked) {
        label.innerHTML = label.innerHTML.replace(from, to).trim();
    } else {
        label.innerHTML = label.innerHTML.replace(to, from).trim();
    }
}


$(document).ready(
    /**
     * When document loads this function inits the keydown function for when the user preses the ENTER key.
     */
    function() {
        $(window).keydown(
            /**
             * Consumes the keydown event (to avoid accidental submission)
             * @param event the keydown event.
             * @returns {boolean} returns false to the keydown function
             */
            function(event) {
                if (event.keyCode === 13 && !allowReload) {
                    event.preventDefault();
                    return false;
                }
            });
    });

/**
 * Prompts the user to confirm reload or resubmission of page.
 * @param e the reload event handler
 * @returns {string} confirmation text
 */
window.onbeforeunload = function(e) {
    if (!allowReload) {
        e = e || window.event; //global event is deprecated D: TODO: find alternative

        // For IE and Firefox prior to version 4
        if (e) e.returnValue = 'Sure?';

        // For Safari
        return 'Sure?';
    }
};