function twig_array_filter(Environment $env, $array, $arrow)
{
    if (!twig_test_iterable($array)) {
        throw new RuntimeError(sprintf('The "filter" filter expects an array or "Traversable", got "%s".', \is_object($array) ? \get_class($array) : \gettype($array)));
    }

    if (!$arrow instanceof Closure && $env->hasExtension('\Twig\Extension\SandboxExtension') && $env->getExtension('\Twig\Extension\SandboxExtension')->isSandboxed()) {
        throw new RuntimeError('The callable passed to "filter" filter must be a Closure in sandbox mode.');
    }

    if (\is_array($array)) {
        return array_filter($array, $arrow, \ARRAY_FILTER_USE_BOTH);
    }

    // the IteratorIterator wrapping is needed as some internal PHP classes are \Traversable but do not implement \Iterator
    return new \CallbackFilterIterator(new \IteratorIterator($array), $arrow);
}
