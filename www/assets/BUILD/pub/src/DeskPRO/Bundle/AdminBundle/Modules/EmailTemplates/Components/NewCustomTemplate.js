import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Input } from 'DeskPRO/Component/Semantic/Form';
import { Button } from 'DeskPRO/Component/Semantic/Button';

class NewCustomTemplate extends React.Component {
  static propTypes = {
    opened:            PropTypes.bool,
    addingNewTemplate: PropTypes.bool,
    close:             PropTypes.func,
    addTemplate:       PropTypes.func,
  };
  static defaultProps = {
    opened: false,
    close() {}
  };

  handleClose = () => {
    this.props.close();
  };

  handleAddTemplate = () => {
    this.props.addTemplate(this.nameInput.input.input.value)
    .then(() => {
      this.props.close();
    });
  };

  render() {
    if (!this.props.opened) {
      return null;
    }
    return (
      <ClickOut onClickOut={this.handleClose}>
        <div className="new-custom-template">
          <h2>Create custom template</h2>
          <span onClick={this.handleClose} className="close"><i className="fa fa-times" /></span>
          <label htmlFor="template_name">Template name: </label><br />
          <Input id="template_name" className="ui input" ref={(c) => { this.nameInput = c; }} />.html<br />
          <span className="help-block">
            Enter a file name for your email template. Valid characters are letters, numbers, hyphens, periods and underscores.
          </span><br />
          <Button
            onClick={this.handleAddTemplate}
            className={classNames({ loading: this.props.addingNewTemplate })}
          >
            Submit
          </Button>
        </div>
      </ClickOut>
    );
  }
}
export default NewCustomTemplate;
