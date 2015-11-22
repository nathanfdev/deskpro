import React, { PropTypes } from 'react';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import classnames from 'classnames';
import jQuery from 'jquery';

export class Title extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    isDone: PropTypes.bool,
    onChange: PropTypes.func,
    editing: PropTypes.bool
  };

  constructor(props) {
    super(props);

    this.state = {
      value: '',
      editing: props.editing || false
    };
  }

  componentDidMount() {
    if (this.props.editing) {
      jQuery(this.refs.input).focus();
    }
  }

  componentDidUpdate() {
    jQuery(this.refs.input).focus();
  }

  onEdit = () => {
    this.setState({
      value: this.props.value,
      editing: true
    });
  };

  onCloseEdit = event => {
    event.preventDefault();

    this.props.onChange(this.state.value);
    this.setState({
      value: '',
      editing: false
    });
  };

  onChange = event => {
    this.setState({
      value: event.target.value
    });
  };

  getValue() {
    return this.state.value || this.props.value;
  }

  renderHeader() {
    return (
      <h1 onDoubleClick={this.onEdit}>
        {this.getValue()}
      </h1>
    );
  }

  renderForm() {
    return (
      <ClickOut onClickOut={this.onCloseEdit}>
        <form className="inline-form" onSubmit={this.onCloseEdit}>
          <h1 className="ignore-react-onclickoutside">
            <input type="text" ref="input" name="title" value={this.getValue()} onChange={this.onChange} />
          </h1>
        </form>
      </ClickOut>
    );
  }

  render() {
    return (
      <div className="card-title">
        <div className={classnames('dpwd--card-title', {'strikethrough': this.props.isDone && !this.state.editing})}>
          {this.state.editing ? this.renderForm() : this.renderHeader()}
        </div>
      </div>
    );
  }
}
