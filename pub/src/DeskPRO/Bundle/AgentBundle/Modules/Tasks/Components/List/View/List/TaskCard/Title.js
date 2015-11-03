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
      value: props.value,
      editing: false
    };
  }

  onEdit = () => {
    this.setState({
      editing: true
    });
  };

  onCloseEdit = event => {
    event.preventDefault();
    this.setState({
      editing: false
    });

    this.props.onChange(this.state.value);
  };

  onChange = event => {
    this.setState({
      value: event.target.value
    });
  };

  renderHeader() {
    return (
      <h1 onDoubleClick={this.onEdit}>
        {this.state.value}
      </h1>
    );
  }

  renderForm() {
    return (
      <ClickOut onClickOut={this.onCloseEdit}>
        <form className="inline-form" onSubmit={this.onCloseEdit}>
          <h1 className="ignore-react-onclickoutside">
            <input type="text" name="title" value={this.state.value} onChange={this.onChange} />
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
