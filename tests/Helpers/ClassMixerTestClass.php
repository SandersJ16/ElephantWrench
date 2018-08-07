<?php

namespace ElephantWrench\Test\Helpers;

class ClassMixerTestClass
{
    public function returnHelloWorldWithOpenBracketOnNewLine()
    {
        return 'hello world';
    }

    public function returnHelloWorldWithOpenBracketOnSameLine() {
        return 'hello world';
    }

    public function returnHelloWorldDefinedOnOneLine() { return 'hello world'; }

    public function returnHelloWorldDefinedOnMultiLinesButStartsOnLineOne() { return 'hello world';
    }

    public function returnHelloWorldWithExtraLinesBetweenMethodBodyAndDefinition()

    {

     return 'hello world';

    }

    public function methodWithRequiredNonTypedHintedNonDefaultValuedParameter($param_1) {}

    public function methodWithStringTypeHintedNonDefaultValuedParameter(string $param_1) {}

    public function methodWithStringTypeHintedDefaultValuedParameter($param_1 = 'default') { return $param_1; }

    public function methodWithStringReturnTypeThatReturnsItsFirstParameter($value) : string
    {
        return $value;
    }

    /*
     ****************************************************************************************
     * The following functions should return true for ClassMixer::hasInstanceContext
     ****************************************************************************************
     */

    public function methodThatUsesNonStaticFunctionCall()
    {
        return $this->returnHelloWorldWithOpenBracketOnNewLine();
    }

    public function methodThatPassesCurrentInstanceToAFunction()
    {
        return get_class($this);
    }

    public function methodWithThisAtEndOfALine() {
        return get_class($this
                         );

    }

    public function methodWithThisInHereDocs()
    {
        $string = <<<TEST
    $this
TEST;
    }

    public function methodWithThisInHereDocsWithBracketEscape()
    {
        $string = <<<TEST2
    {$this}
TEST2;
    }

    public function methodWithThisInHereDocsWithBracketEscapeAndFunctionCall()
    {
        $string = <<<TEST3
    {$this->returnHelloWorldWithExtraLinesBetweenMethodBodyAndDefinition()}
TEST3;
    }

    public function methodWithThisInDoubleQuotes()
    {
        $string = "$this";
    }

    public function methodWithThisInDoubleQuotesWithBracketEscape()
    {
        $string = "{$this}";
    }

    public function methodWithThisInDoubleQuotesWithBracketEscapeAndFunctionCall()
    {
        $string = "{$this->returnHelloWorldWithExtraLinesBetweenMethodBodyAndDefinition()}";
    }

    public function methodWithThisInDoubleQuotesBrokenOntoMultipleLines()
    {
        $string = "
$this";
    }

    public function methodWithThisInDoubleQuotesWithBracketEscapeBrokenOntoMultipleLines()
    {
        $string = "
{$this}";
    }

    public function methodWithThisInDoubleQuotesWithBracketEscapeAndFunctionCallBrokenOntoMultipleLines()
    {
        $string = "
        {$this->returnHelloWorldWithExtraLinesBetweenMethodBodyAndDefinition()}
";
    }

    /*
     ****************************************************************************************
     * The following functions should return false for ClassMixer::hasInstanceContext
     ****************************************************************************************
     */

    public function methodThatHasThisInSingleLineComments()
    {
        //$this is not used
        //even on this line $this is not used
        #Comments with Hashes as well ignore $this
        //When it is at the end as well it'll fail $this
        return;
    }

    public function methodThatHasThisInBlockComments()
    {
        /* Single line block comment with $this */
        /**
         * Block Comments too should ignore $this (this'll be hard)
         */

        /*
            Another block comment without extra stars should ignore $this;
         */
        return;
    }

    public function methodThatHasThisInNowDoc()
    {
        return <<<'TEST'
        $this->printhello();
TEST;
    }

    public function methodWithEscapedDollarSignThisInDoubleQuotes()
    {
        return "\$this";
    }

    public function methodWithEscapedDollarSignThisInHereDoc()
    {
        return <<<DOC
        \$this
DOC;
    }

    public function methodWithVariableStartingWithThis()
    {
        $this_var = 'this';
    }

    /**
     * Used to allow $this in  a string substitution
     *
     * @return string
     */
    public function __toString()
    {
        return 'A String';
    }
}
