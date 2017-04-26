import Widget from './Widget';

class VariableWidget extends Widget {
  constructor(cm, pos, code, text) {
    super(cm, pos);
    this.code = code;
    const element = document.createElement('span');
    element.innerHTML = text;
    element.className = 'twig-variable';
    element.onclick = this.handleClick;
    this.setMark(element);
  }

  handleClick = () => {
    console.log(this);
  };
}
export default VariableWidget;
