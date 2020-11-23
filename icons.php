<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <link rel="stylesheet" href="./css/css/all.css">
    <meta charset="utf-8">
    <title>Icons</title>

    <style media="screen">
    table, th, td {
border: 1px solid black;
}
    </style>
  </head>
  <body>

    <table>
      <col>
      <colgroup span="5"></colgroup>
      <thead>
        <tr>
          <th scope="col">Icon</th>
          <th colspan="5" scope="colgroup">Triggers</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th><i class="fas fa-book"></i></th>
          <td>book</td>
          <td>libro</td>
        </tr>
        <tr>
          <th><i class="fas fa-book-reader"></i></th>
          <td>novel</td>
          <td>novela</td>
        </tr>
        <tr>
          <th><i class="fas fa-paint-brush"></i></th>
          <td>art</td>
          <td>arte</td>
        </tr>
        <tr>
          <th><i class="far fa-image"></i></th>
          <td>photo</td>
          <td>picture</td>
          <td>foto</td>
        </tr>
        <tr>
          <th><i class="far fa-newspaper"></i></th>
          <td>newspaper</td>
          <td>periódico</td>
          <td>periodico</td>
        </tr>
        <tr>
          <th><i class="fas fa-book-open"></i></th>
          <td>magazine</td>
          <td>revista</td>
        </tr>
        <tr>
          <th><i class="fas fa-file-invoice"></i></th>
          <td>document</td>
          <td>documento</td>
        </tr>
        <tr>
          <th><i class="far fa-file-word"></i></th>
          <td>word document</td>
          <td>word</td>
          <td>docx</td>
          <td>doc</td>
        </tr>
        <tr>
          <th><i class="far fa-file-powerpoint"></i></th>
          <td>powerpoint presentation</td>
          <td>powerpoint</td>
          <td>pptx</td>
          <td>ppt</td>
        </tr>
        <tr>
          <th><i class="far fa-file-excel"></i></th>
          <td>excel spreadsheet</td>
          <td>excel</td>
          <td>xlsx</td>
          <td>xls</td>
        </tr>
        <tr>
          <th><i class="fas fa-file-csv"></i></th>
          <td>comma-separated values</td>
          <td>comma separated values</td>
          <td>csv</td>
        </tr>
        <tr>
          <th><i class="fas fa-file-pdf"></i></th>
          <td>pdf</td>
        </tr>
        <tr>
          <th><i class="fas fa-file-archive"></i></th>
          <td>archive</td>
          <td>zip</td>
        </tr>
        <tr>
          <th><i class="fas fa-file-code"></i></th>
          <td>programming</td>
          <td>code</td>
          <td>código</td>
          <td>codigo</td>
        </tr>
        <tr>
          <th><i class="far fa-file-video"></i></th>
          <td>video</td>
          <td>movie</td>
          <td>animation</td>
          <td>película</td>
          <td>pelicula</td>
        </tr>
        <tr>
          <th><i class="far fa-file-audio"></i></th>
          <td>audio</td>
          <td>song</td>
          <td>music</td>
          <td>canción</td>
          <td>cancion</td>
        </tr>
        <tr>
          <th><i class="fas fa-photo-video"></i></th>
          <td>media</td>
        </tr>
        <tr>
          <th><i class="fas fa-atlas"></i></th>
          <td>atlas</td>
          <td>map</td>
          <td>mapa</td>
        </tr>
        <tr>
          <th><i class="fas fa-bible"></i></th>
          <td>bible</td>
          <td>biblia</td>
        </tr>
        <tr>
          <th><i class="fas fa-quran"></i></th>
          <td>quran</td>
          <td>corán</td>
          <td>coran</td>
        </tr>
        <tr>
          <th><i class="fas fa-torah"></i></th>
          <td>torah</td>
          <td>tora</td>
        </tr>
        <tr>
          <th><i class="far fa-file-alt"></i></th>
          <td><i>[default icon]</i></td>
          <td><i>[icono por defecto]</i></td>
        </tr>
      </tbody>
    </table>

<script>
    function ico(str) {
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
            case "codigo":
            case "código":
            case "programming":
                return '<i class="fas fa-file-code"></i>';

            case "video":
            case "vídeo":
            case "película":
            case "pelicula":
            case "movie":
            case "animation":
                return '<i class="far fa-file-video"></i>';

            case "audio":
            case "cancion":
            case "canción":
            case "song":
            case "music":
                return '<i class="far fa-file-audio"></i>';

            case "media":
                return '<i class="fas fa-photo-video"></i>';

            case "atlas":
            case "map":
            case "mapa":
                return '<i class="fas fa-atlas"></i>';

            case "biblia":
            case "bible":
                return '<i class="fas fa-bible"></i>';

            case "coran":
            case "corán":
            case "quran":
                return '<i class="fas fa-quran"></i>';

            case "tora":
            case "torah":
                return '<i class="fas fa-torah"></i>';

            default:
                return '<i class="far fa-file-alt"></i>';
        }
    }
    </script>
  </body>
</html>
