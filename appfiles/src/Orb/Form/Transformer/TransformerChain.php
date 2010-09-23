<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Form\Transformer;


/**
 * Transformer composite
 */
class TransformerChain implements TransformerInterface
{
    /**
     * The transformers
     * @var array
     */
    protected $transformers = array();



	/**
	 * Add a new transformer to the chain
	 * 
	 * @param \Orb\Form\Transformer\TransformerInterface $transformer
	 */
	public function addTransformer(\Orb\Form\Transformer\TransformerInterface $transformer)
	{
		$this->transformers[] = $transformer;
	}



	/**
	 * Get an array of currently set transformers.
	 *
	 * @return array
	 */
	public function getTransformers()
	{
		return $this->transformers;
	}


	
    public function transform($value)
    {
        foreach ($this->transformers as $transformer) {
            $value = $transformer->transform($value);
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        for ($i = count($this->transformers) - 1; $i >= 0; --$i) {
            $value = $this->transformers[$i]->reverseTransform($value);
        }

        return $value;
    }
}
