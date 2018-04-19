import React from 'react';
import PropTypes from 'prop-types';
import { Icon, Popper } from '@deskpro/react-components';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';

/**
 * Filter icon and popper which will contain a form that filters a list
 */
export default class ItemFilter extends React.Component {
  static propTypes = {
    /**
     * Whether the popper is opened or not.
     */
    opened:   PropTypes.bool,
    /**
     * One or more components.
     */
    children: PropTypes.node,
  };

  static defaultProps = {
    opened:   false,
    children: ''
  };

  constructor(props) {
    super(props);
    this.state = {
      popper: null // eslint-disable-line
    };
  }

  componentDidMount() {
    this.setState({ popper: this.popper }); // eslint-disable-line
  }

  /**
   * Toggles the popper opened or closed
   */
  togglePopup = () => {
    this.popper.toggle();
  };

  /**
   * Opens the popper
   */
  open = () => {
    this.popper.open();
  };

  /**
   * Closes the popper
   */
  close = () => {
    if (this.popper) {
      this.popper.close();
    }
  };

  /**
   * Renders the standard popper
   */
  renderPopper() {
    const { children, opened } = this.props;

    return (
      <Popper
        ref={(ref) => { this.popper = ref; }}
        opened={opened}
        offsetX="2px"
        offsetY="3px"
        className="dp-column-popper"
        placement="bottom"
        style={{ width: 180 }}
        closeOnClickOutside
        preventOverflow
      >
        {children}
      </Popper>
    );
  }

  render() {
    const { children } = this.props;
    return (
      <span onClick={this.togglePopup}>
        <PopUp
          positionMy="center top"
          positionAt="center bottom"
          zIndex={99999}
          ref={(c) => { this.popup = c; }}
          content={children}
          autoOpen={false}
          className="dp-icon__filter dp-filters-popup"
        >
          <Icon
            name="filter"
          />
        </PopUp>
      </span>
    );
  }
}
