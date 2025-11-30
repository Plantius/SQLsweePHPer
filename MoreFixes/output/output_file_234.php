function twig_array_reduce(Environment $env, $array, $arrow, $initial = null)
{
    if (!$arrow instanceof Closure && $env->hasExtension('\Twig\Extension\SandboxExtension') && $env->getExtension('\Twig\Extension\SandboxExtension')->isSandboxed()) {
        throw new RuntimeError('The callable passed to the "reduce" filter must be a Closure in sandbox mode.');
    }

    if (!\is_array($array)) {
        if (!$array instanceof \Traversable) {
            throw new RuntimeError(sprintf('The "reduce" filter only works with arrays or "Traversable", got "%s" as first argument.', \gettype($array)));
        }

        $array = iterator_to_array($array);
    }

    return array_reduce($array, $arrow, $initial);
}
