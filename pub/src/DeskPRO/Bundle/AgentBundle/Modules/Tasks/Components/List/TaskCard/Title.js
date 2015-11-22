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
      value: props.value,
      editing: props.editing || false
    };
  }

  componentDidMount() {
    if (this.props.editing) {
      jQuery(this.refs.input).focus();
    }
  }

  componentWillReceiveProps() {
    this.setState({
      title: this.props.value
    });
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

    if (!this.state.value) {
      if (this.props.value) {
        this.setState({
          editing: false,
          value: this.props.value
        });
      }

      return;
    }

    this.props.onChange(this.state.value);
    this.setState({
      editing: false
    });
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
            <input type="text" ref="input" name="title" value={this.state.value} onChange={this.onChange} />
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
