// open pdf in new window
// function openPDFDDL(base64URL) {
//     let win = window.open();
//     win.document.write('<style>body{margin:0;}iframe{display:block;background: white;border: none;height: 100vh;width: 100vw;}</style>');
//     win.document.write('<iframe src="' + base64URL + '" frameborder="0" style="border:0; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%;" allowfullscreen>Your browser does not support iframe. Please <a href="' + base64URL + '" download alt="link to download pdf">download the pdf</a>.</iframe>');
// }

// function openPDFPHP(item_id, download, name) {
//     let href = window.location.href;
//     if (href.endsWith('index.php')) {
//          href = href.replace('index.php', "");
//     }
//
//     if (!href.endsWith('/')) {
//         let paths = href.split('/');
//         href = href.replace(paths.pop(), "");
//     }
//     window.open(`${href}file.php?id=${item_id}&download=${download}&name=${name}`);
// }