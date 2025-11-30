function sendDocument($documentId, $courseInfo)
{
    $courseId = $courseInfo['real_id'];

    compilatioUpdateWorkDocument($documentId, $courseId);
    $workTable = Database::get_course_table(TABLE_STUDENT_PUBLICATION);
    $query = "SELECT * FROM $workTable
              WHERE id = $documentId AND c_id= $courseId";
    $sqlResult = Database::query($query);
    $doc = Database::fetch_object($sqlResult);
    $currentCourseRepositoryWeb = api_get_path(WEB_COURSE_PATH).$courseInfo['path'].'/';
    $documentUrl = $currentCourseRepositoryWeb.$doc->url;

    $filePath = $courseInfo['course_sys_path'].$doc->url;
    $mime = DocumentManager::file_get_mime_type($doc->title);

    $compilatio = new Compilatio();
    if ('wget' === $compilatio->getTransportMode()) {
        if (strlen($compilatio->getWgetUri()) > 2) {
            $filename = preg_replace('/$', '', $compilatio->getWgetUri()).'/'.$courseInfo['path'].'/'.$doc->title;
        } else {
            $filename = $documentUrl;
        }
        if (strlen($compilatio->getWgetLogin()) > 2) {
            $filename = $compilatio->getWgetLogin().':'.$compilatio->getWgetPassword().'@'.$filename;
        }
        $compilatioId = $compilatio->sendDoc($doc->title, '', $filename, 'text/plain', 'get_url');
    } else {
        $pieces = explode('/', $doc->url);
        $nbPieces = count($pieces);
        $filename = $pieces[$nbPieces - 1];
        $compilatioId = $compilatio->sendDoc($doc->title, '', $filename, $mime, file_get_contents($filePath));
    }

    if (Compilatio::isMd5($compilatioId)) {
        $compilatio->saveDocument($courseId, $doc->id, $compilatioId);
        $compilatio->startAnalyse($compilatioId);
        echo Display::return_message(get_lang('Uploaded'));
    } else {
        echo Display::return_message(get_lang('Error'), 'error');
    }
}
