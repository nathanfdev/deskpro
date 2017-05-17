import React, { PropTypes } from 'react';
import { render } from 'react-dom';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import Widget from './Widget';
import CodeMirror from '../CodeMirror';

class TemplatePopup extends React.Component {
  static propTypes = {
    text:             PropTypes.string,
    loadTemplate:     PropTypes.func,
    setCurrentWidget: PropTypes.func,
    setValue:         PropTypes.func,
  };
  static defaultProps = {
    loadTemplate() {},
    setValue() {},
  };

  constructor(props) {
    super(props);
    this.state = {
      content:       '',
      currentWidget: ''
    };
  }

  // Local function to deal with widget inception
  setCurrentWidget = (popup) => {
    if (this.state.currentWidget && this.state.currentWidget !== popup) {
      this.state.currentWidget.closePopup();
    }
    this.setState({
      currentWidget: popup
    });
  };

  getPopUp = () => (
    <div className="template-popup">
      <CodeMirror
        value={this.state.content}
        onChange={this.handleChange}
        ref={(c) => { this.widgetEditor = c; }}
      />
      <Button onClick={this.saveChanges}>Submit</Button>
      <Button className="basic" onClick={this.closePopup}>Cancel</Button>
    </div>
    );

  handleChange = (cm, change) => {
    this.props.addMarks(cm, change, this.setCurrentWidget);
    this.setState({
      content: cm.getValue()
    });
  };

  loadContent = () => {
    this.props.loadTemplate(this.props.text).then(
      (content) => {
        this.setState({
          content,
        });
      }
    );
  };

  openPopup = () => {
    this.loadContent();
    this.popup.openPopup();
    setTimeout(() => {
      this.props.setCurrentWidget(this.popup, this.widgetEditor);
    }, 100);
  };

  closePopup = () => {
    this.popup.closePopup();
    this.props.setCurrentWidget(null, null);
  };

  saveChanges = () => {
    this.props.setValue(this.props.text, this.state.content);
    this.popup.closePopup();
    this.props.setCurrentWidget(null, null);
  };

  render() {
    return (
      <PopUp
        positionMy="left top-1px"
        positionAt="left bottom"
        zIndex={100}
        content={this.getPopUp()}
        ref={(c) => { this.popup = c; }}
        style={{ display: 'inline-block' }}
        clickOut={false}
        manual
      >
        <span
          onClick={this.openPopup}
        >
          {this.props.text.replace(/^SendmailBundle:/, '')}
        </span>
      </PopUp>
    );
  }
}
class TemplateWidget extends Widget {
  constructor(cm, pos, code, text, setCurrentWidget, loadTemplate, setValue) {
    super(cm, pos);
    try {
      const element = document.createElement('span');
      element.className = 'twig-include';
      this.setMark(element, code);

      render(
        <TemplatePopup
          text={text}
          addMarks={addMarks}
          loadTemplate={loadTemplate}
          setCurrentWidget={setCurrentWidget}
          setValue={setValue}
        />,
        element
      );
    } catch (e) {
      console.error(e);
    }
  }

  handleClick = () => {
    console.log(this);
  };
}
export default TemplateWidget;
