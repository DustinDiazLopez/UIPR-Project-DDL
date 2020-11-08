/**
 * Converts the base64 data into a Blob.
 * @param {string} b64Data base64 encoded data
 * @param {string} contentType document type (mime type) of the encoded data
 * @param {number} sliceSize buffer size to parse through the data
 * @returns {Blob} returns the blob of the base54 encoded data
 */
function b64toBlob(b64Data, contentType, sliceSize) {
    const byteCharacters = atob(b64Data);
    const byteArrays = [];

    for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
        const slice = byteCharacters.slice(offset, offset + sliceSize);

        const byteNumbers = new Array(slice.length);
        for (let i = 0; i < slice.length; i++) {
            byteNumbers[i] = slice.charCodeAt(i);
        }

        byteArrays.push(new Uint8Array(byteNumbers));
    }

    return new Blob(byteArrays, {
        type: contentType
    });
}