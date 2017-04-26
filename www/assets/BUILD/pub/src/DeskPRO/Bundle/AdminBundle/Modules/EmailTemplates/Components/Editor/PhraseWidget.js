import Widget from './Widget';

class PhraseWidget extends Widget {
  constructor(cm, pos, code, text) {
    super(cm, pos);
    this.code = code;
    const element = document.createElement('span');
    element.innerHTML = text;
    element.className = 'twig-phrase';
    element.onclick = this.handleClick;
    this.setMark(element);

    const matches = code.match(/{{\s*phrase\('([^)]+)'(,\s*{[^}]+})?\)\s*}}/);
    this.phrase = matches[1];
    if (matches[2]) {
      this.variables = [];
      const variablesMatches = matches[2].substring(3, matches[2].length - 1);
      console.log(variablesMatches);
    }
  }

  handleClick = () => {
    console.log(this);
  };
}
export default PhraseWidget;
