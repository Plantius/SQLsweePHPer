function core_purify_string($input)
{
    global $purifier;
    return trim($purifier->purify($input));
}
