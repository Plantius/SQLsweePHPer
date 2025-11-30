    public function enrichLayoutDefinition(/* ?Concrete */ $object, /* array */ $context = []) // : static
    {
        $renderer = Model\DataObject\ClassDefinition\Helper\DynamicTextResolver::resolveRenderingClass(
            $this->getRenderingClass()
        );

        $context['fieldname'] = $this->getName();
        $context['layout'] = $this;

        if ($renderer instanceof DynamicTextLabelInterface) {
            $result = $renderer->renderLayoutText($this->renderingData, $object, $context);
            $this->html = $result;
        }

        $templatingEngine = \Pimcore::getContainer()->get('pimcore.templating.engine.delegating');
        $twig = $templatingEngine->getTwigEnvironment();
        $template = $twig->createTemplate($this->html);
        $this->html = $template->render(array_merge($context,
            [
                'object' => $object,
            ]
        ));

        return $this;
    }
