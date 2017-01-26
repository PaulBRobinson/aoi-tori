<?php

/* standard */
class __TwigTemplate_ca5829a333f7014532fd78e7ababa1a2ca645e19c2bc0ebb070c107c6ebdfd05 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<h3>Tweets - Custom Template</h3>
";
        // line 2
        if ((twig_length_filter($this->env, (isset($context["tweets"]) ? $context["tweets"] : null)) > 0)) {
            // line 3
            echo "  <ul>
    ";
            // line 4
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["tweets"]) ? $context["tweets"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["tweet"]) {
                // line 5
                echo "      <li>";
                echo twig_escape_filter($this->env, $this->getAttribute($context["tweet"], "text", array()), "html", null, true);
                echo "</li>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['tweet'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 7
            echo "  </ul>
";
        }
        // line 9
        echo "      ";
    }

    public function getTemplateName()
    {
        return "standard";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  44 => 9,  40 => 7,  31 => 5,  27 => 4,  24 => 3,  22 => 2,  19 => 1,);
    }
}
/* <h3>Tweets - Custom Template</h3>*/
/* {% if tweets|length > 0 %}*/
/*   <ul>*/
/*     {% for tweet in tweets %}*/
/*       <li>{{ tweet.text }}</li>*/
/*     {% endfor %}*/
/*   </ul>*/
/* {% endif %}*/
/*       */
