import PropTypes from 'prop-types';
import React from 'react'; // eslint-disable-line no-unused-vars


/**
 * @param {SyntheticEvent} ev
 */
function cancelMouseEvent(ev) {
  ev.preventDefault();
  ev.stopPropagation();
}

export default class Icon extends React.PureComponent {
  static propTypes = {

    /**
     * If set, a number that will be displayed in one of the top corners
     */
    notification: PropTypes.number,

    /**
     * A string indicating how the notification is displayed
     */
    notificationStyle: PropTypes.oneOf(['urgent', 'standard']),

    /**
     * The url of the image to be displayed
     */
    iconUrl: PropTypes.string.isRequired,

    /**
     * The title of the app
     */
    appTitle: PropTypes.string,

    /**
     * If set indicates the component should display a separator
     */
    showSeparator: PropTypes.bool,

    /**
     * A callback handler
     */
    onClick: PropTypes.func,

  };

  static defaultProps = {
    showSeparator:     false,
    notificationStyle: 'urgent'
  };

  /**
   * @param {SyntheticEvent} ev
   */
  onClick = (ev) => {
    const { onClick, ...others } = this.props;
    if (typeof onClick === 'function') {
      cancelMouseEvent(ev);
      onClick({ ...others });
    }
  };

  render()  {
    const buttonClassname = [
      'dp-ButtonTabs',
      this.props.showSeparator ? 'dp----with-separator' : '',
      'dp---is-pulse',
    ].join(' ');

    const imgClassname = [
      'dp-ButtonsImg',
      !this.props.notification ? 'dp-ButtonsImg--isInactive' : '',
    ].join(' ');

    const badgeStyle = [
      'dp-IconBadge',
      this.props.notificationStyle === 'standard' ? 'is-inactive' : ''
    ].join(' ');

    return (
      <button className={buttonClassname} onMouseOver={cancelMouseEvent} onClick={this.onClick}>
        <img src={this.props.iconUrl} alt={this.props.appTitle} className={imgClassname} />
        { !!this.props.notification && <span className={badgeStyle}>{this.props.notification}</span> }
      </button>
    );
  }
}
