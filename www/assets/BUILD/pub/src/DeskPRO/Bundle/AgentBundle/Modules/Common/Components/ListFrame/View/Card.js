import PropTypes from 'prop-types';
import React, { Component } from 'react';
import classNames from 'classnames';
import { injectIntl, intlShape, FormattedRelative } from 'react-intl';
import Immutable from 'immutable';

export class Card extends Component {

  static propTypes = {
    additionalClasses: PropTypes.string,
    children:          PropTypes.any,
    minimized:         PropTypes.bool,
    moving:            PropTypes.bool,
    type:              PropTypes.string.isRequired,
    width:             PropTypes.number
  };

  render() {
    const { type, moving, minimized, width, additionalClasses } = this.props;
    const classes = classNames(
      'dpmw--single-card',
      additionalClasses,
      { 'dpmw--single-task-card': type === 'task', floating: type === 'float', minimized, moving }
    );

    const styles = {};
    if (width) {
      styles.width = width;
    }

    return (
      <div className={classes} style={styles}>
        {this.props.children}
      </div>
    );
  }
}

export class CardLine extends Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="dpw--card-line">
        {this.props.children}
      </div>
    );
  }
}

export class CardLineLeft extends Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="dpw--card-line-left">
        {this.props.children}
      </div>
    );
  }
}

export class CardLineRight extends Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="dpw--card-line-right">
        {this.props.children}
      </div>
    );
  }
}

export class CardLineFull extends Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="dpw--card-line-full">
        {this.props.children}
      </div>
    );
  }
}

export class CardContentText extends Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div className="dpwd--card-content-text">
        {this.props.children}
      </div>
    );
  }
}

export class CardLineItem extends Component {

  static propTypes = {
    clickParams: PropTypes.object,
    onClick:     PropTypes.func,
    children:    PropTypes.any,
    icon:        PropTypes.string
  };

  handleClick = () => {
    const { clickParams, onClick } = this.props;
    if (clickParams && onClick) {
      onClick(clickParams);
    }
  };

  render() {
    const { icon } = this.props;
    let classes = '';

    if (icon) {
      classes = classNames('fa', icon);
    }

    return (
      <span className="dpwd--card-line-item" onClick={this.handleClick}>
         {icon ? <i className={classes} /> : ''} {this.props.children}
      </span>
    );
  }
}

export class CardCheckbox extends Component {

  static propTypes = {
    onClick:  PropTypes.func,
    selected: PropTypes.bool
  };

  render() {
    const { selected, onClick } = this.props;
    const classes = classNames('fa', { 'fa-check': selected });

    return (
      <div className="dpm--card-checkbox" onClick={onClick}>
        <i className={classes} />
      </div>
    );
  }
}

export class CardReset extends Component {

  static propTypes = {
    onReset: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { changed: false };
  }

  shouldComponentUpdate(props, state) {
    return state.changed !== this.state.changed;
  }

  onChange = () => {
    this.setState({ changed: true });
  };

  onReset = () => {
    this.setState({ changed: false });
    this.props.onReset();
  };

  render() {
    const { changed } = this.state;

    return (
      <div className="dpm--card-reset" onClick={this.onReset}>
        <i className={classNames('fa fa-trash', { active: changed })} />
      </div>
    );
  }
}

export class CardDisc extends Component {

  render() {
    return <span className="dpw--card-disc" />;
  }
}

export class CardStatusBar extends Component {

  static propTypes = {
    align: PropTypes.string.isRequired,
    level: PropTypes.string.isRequired
  };

  render() {
    const { align, level } = this.props;
    const classes = classNames('dpw--card-status-bar', `level-${level}`,
                               { 'dpw--status-bar-left': align === 'left', 'dpw--status-bar-right': align === 'right' }
    );

    return <div className={classes}></div>;
  }
}

export class CardTitle extends Component {

  static propTypes = {
    content: PropTypes.string.isRequired
  };

  render() {
    const { content } = this.props;
    let substr = content.substr(0, 40);
    if (content.length > 40) {
      substr += '...';
    }
    return (
      <div className="dpwd--card-title">
        <h1>{substr}</h1>
      </div>
    );
  }
}

@injectIntl
export class CardDate extends Component {

  static propTypes = {
    intl:  intlShape.isRequired,
    date:  PropTypes.string.isRequired,
    label: PropTypes.string
  };

  render() {
    const { label, date } = this.props;
    return (
      <span>
        {label ? `${label}: ` : ''} <FormattedRelative value={date} />
      </span>
    );
  }
}

export class CardUser extends Component {

  static propTypes = {
    email: PropTypes.string,
    user:  PropTypes.object
  };

  render() {
    const user = this.props.user || Immutable.fromJS({});
    const { email } = this.props;

    return (
      <div className="dpwd--card-line-item">
        <i className="fa fa-user" /> {user.get('first_name')} {user.get('last_name')}
        {email ? <CardDisc /> : ''}
        {email ? <span>{email}</span> : ''}
      </div>
    );
  }
}

export class CardLabel extends Component {

  static propTypes = {
    label: PropTypes.string.isRequired
  };

  render() {
    return (
      <span><a href="#">{this.props.label}</a>, </span>
    );
  }
}
export class CardComments extends Component {

  static propTypes = {
    commentsCounter: PropTypes.number.isRequired
  };

  render() {
    const { commentsCounter } = this.props;

    return (
      <CardLineItem>
        {commentsCounter} <i className="fa fa-comments-o" />
      </CardLineItem>
    );
  }
}

export class CardGroupDivider extends Component {

  static propTypes = {
    title: PropTypes.string
  };

  render() {
    return (
      <div className="divider">
        <hr />
        <h1>
          <span>{this.props.title}</span>
        </h1>
      </div>
    );
  }
}
