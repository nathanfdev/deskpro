import React, { PropTypes } from 'react';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import classNames from 'classnames';
import jQuery from 'jquery';

export class Title extends React.Component {

  static propTypes = {
    value: PropTypes.string,
    isDone: PropTypes.bool,
    onChange: PropTypes.func,
    onSave: PropTypes.func,
    onSetEditing: PropTypes.func,
    editing: PropTypes.bool
  };

  constructor(props) {
    super(props);

    this.state = {
      error: false,
      value: props.value,
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
    const { value, onSetEditing } = this.props;
    this.setState({
      value: value,
      editing: true
    });

    if (onSetEditing) {
      onSetEditing(true);
    }
  };

  onCloseEdit = event => {
    event.preventDefault();

    // Skip on click on the input field
    if (jQuery(this.refs.input).is(event.target)) {
      return;
    }

    const { value, onChange, onSave, onSetEditing } = this.props;

    // Prevent sending empty data or set default value if it exists
    if (!this.state.value) {
      if (value) {
        this.setState({
          editing: false,
          value: value
        });
      } else {
        this.setState({
          error: true
        });
      }

      return;
    }

    this.setState({
      editing: false
    });

    onChange(this.state.value);
    if (onSetEditing) {
      onSetEditing(false);
    }

    // Trigger save callback if we clicked on the save task button
    if (onSave && jQuery('.dpw--single-card-mark-done').has(event.target).length) {
      onSave();
    }
  };

  onChange = event => {
    this.setState({
      value: event.target.value,
      error: false
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
      <ClickOut onClickOut={this.onCloseEdit}
                onClick={this.onCloseEdit}
                additionalNodes={['.dpw--single-card-mark-done']}>

        <form className="inline-form" onSubmit={this.onCloseEdit}>
          <input type="text"
                 ref="input"
                 name="title"
                 value={this.state.value}
                 className={classNames({'error': this.state.error})}
                 onChange={this.onChange} />
        </form>
      </ClickOut>
    );
  }

  render() {
    return (
      <div className="card-title">
        <div className={classNames(
          'dpwd--card-title',
          {'strikethrough': this.props.isDone && !this.state.editing}
        )}>

          {this.state.editing ? this.renderForm() : this.renderHeader()}
        </div>
      </div>
    );
  }
}
