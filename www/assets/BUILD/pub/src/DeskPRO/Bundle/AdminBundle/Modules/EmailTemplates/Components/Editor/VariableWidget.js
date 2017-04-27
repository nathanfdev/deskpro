import Widget from './Widget';

class VariableWidget extends Widget {
  constructor(cm, pos, code, text) {
    super(cm, pos);
    const element = document.createElement('span');
    element.innerHTML = text;
    element.className = 'twig-variable';
    element.onclick = this.handleClick;
    this.setMark(element, code);
  }

  handleClick = () => {
    console.log(this);
  };
}
export default VariableWidget;
