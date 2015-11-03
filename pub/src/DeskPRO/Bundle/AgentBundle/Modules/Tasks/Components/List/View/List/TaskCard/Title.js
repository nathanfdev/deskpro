import React, { PropTypes } from 'react';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';

export class Title extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      editing: false
    };
  }

  onEdit = () => {
    this.setState({
      editing: true
    });
  };

  onCloseEdit = () => {
    this.setState({
      editing: false
    });
  };

  onChange = event => {
    this.props.onChange(event.target.value);
  };

  renderHeader() {
    return (
      <h1 onDoubleClick={this.onEdit}>
        {this.props.value}
      </h1>
    );
  }

  renderForm() {
    return (
      <ClickOut onClickOut={this.onCloseEdit}>
        <form className="inline-form">
          <h1 className="ignore-react-onclickoutside">
            <input type="text" name="title" value={this.props.value} onChange={this.onChange} />
          </h1>
        </form>
      </ClickOut>
    );
  }

  render() {
    return (
      <div className="card-title">
        <div className="dpwd--card-title strikethrough">
          {this.state.editing ? this.renderForm() : this.renderHeader()}
        </div>
      </div>
    );
  }
}
