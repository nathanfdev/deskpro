import React, { PropTypes } from 'react';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { TitleForm } from './TitleForm';
import classNames from 'classnames';
import { CardWidget } from './CardWidget';

export class Title extends React.Component{

  static propTypes = {
    value: PropTypes.string,
    isDone: PropTypes.bool,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {isOpen: false};
    this.value = props.value;
  }

  shouldComponentUpdate(nextProps, nextState) {
    return this.value !== nextProps.value || this.state.isOpen !== nextState.isOpen;
  }

  componentWillUpdate(nextProps, nextState) {
    this.value = nextProps.value;
  }

  onChange = (val) => {
    this.value = val;
  };

  onOpen = () => {
    if (this.state.isOpen) return;
    this.setState({isOpen: true});
  };

  onClose = () => {
    if (!this.state.isOpen) return;
    this.setState({isOpen: false});
    this.props.onChange && this.props.onChange(this.value);
  };

  renderHeader() {
    return (
      <h1 onDoubleClick={this.onOpen}>
        {this.value}
      </h1>
    );
  }

  renderForm() {
    return (
      <ClickOut onClickOut={this.onClose}>
        <TitleForm value={this.value} onChange={this.onChange} onSubmit={this.onClose} />
      </ClickOut>
    );
  }

  render() {
    return (
      <div className="card-title">
        <div className={classNames(
          'dpwd--card-title',
          {'strikethrough': this.props.isDone && !this.state.isOpen}
        )}>

          {this.state.isOpen ? this.renderForm() : this.renderHeader()}
        </div>
      </div>
    );
  }
}
