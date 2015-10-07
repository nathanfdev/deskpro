import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';
import { injectIntl, intlShape, FormattedRelative } from 'react-intl';

export class Card extends Component {

  static propTypes = {
    type: PropTypes.string.isRequired,
    moving: PropTypes.bool,
    minimized: PropTypes.bool
  };

  render() {
    const {type, moving, minimized} = this.props;
    var classes = classNames('dpmw--single-card', {
      'dpmw--single-task-card': type === 'task',
      'floating': type === 'float',
      'minimized': minimized,
      'moving': moving
    });

    return (
      <div className={classes}>
        {this.props.children}
      </div>
    );
  }
}

export class CardLine extends Component {

  render() {
    return (
      <div className="dpw--card-line">
        {this.props.children}
      </div>
    );
  }
}

export class CardLineLeft extends Component {

  render() {
    return (
      <div className="dpw--card-line-left">
        {this.props.children}
      </div>
    );
  }
}

export class CardLineRight extends Component {

  render() {
    return (
      <div className="dpw--card-line-right">
        {this.props.children}
      </div>
    );
  }
}

export class CardLineItem extends Component {

  static propTypes = {
    icon: PropTypes.string
  };

  render() {
    const {icon} = this.props;
    if (icon) {
      var classes = classNames('fa', icon);
    }
    return (
      <span className="dpwd--card-line-item">
         {icon ? <i className={classes}/> : ''} {this.props.children}
      </span>
    );
  }
}

export class CardCheckbox extends Component {

  render() {
    const { selected, onClick } = this.props;
    var classes = classNames('fa', {'fa-check': selected});

    return (
      <div className="dpm--card-checkbox" onClick={onClick}>
        <i className={classes}></i>
      </div>
    );
  }
}

export class CardDisc extends Component {

  render() {
    return (
      <span className="dpw--card-disc"/>
    );
  }
}

export class CardTitle extends Component {

  static propTypes = {
    content: PropTypes.string.isRequired
  };

  render() {
    const {content} = this.props;
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
    intl: intlShape.isRequired,
    date: PropTypes.string.isRequired,
    label: PropTypes.string
  };

  render() {
    const {label, date} = this.props;
    return (
      <span>
        {label ? `${label}: ` : ''} <FormattedRelative value={date}/>
      </span>
    );
  }
}

export class CardUser extends Component {

  static propTypes = {
    email: PropTypes.string.isRequired,
    user: PropTypes.object
  };

  render() {
    const user = this.props.user || {};
    const {email} = this.props;
    return (
      <div className="dpwd--card-assigned">
              <span className="dpw--avatar-face" style={{backgroundImage: 'url(../img/avatars/avatar6.png)'}}>
                {user.first_name} {user.last_name}
              </span>
        <CardDisc/>
        {email ? <span>{email}</span> : ''}
      </div>
    );
  }
}

