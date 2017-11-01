import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class TypeSlider extends React.Component {

  static propTypes = {
    id:         PropTypes.string,
    label:      PropTypes.string,
    active:     PropTypes.bool,
    toggleType: PropTypes.func
  };

  onClick = event => {
    event.preventDefault();

    const { toggleType, id } = this.props;
    toggleType(id);
  };

  render() {
    const { id, active, label } = this.props;

    return (
      <li>
        <div className="slider-panel">
          <a
            href={`/feedback/browse/type-${id}`}
            className={classNames('slider', { off: !active })}
            onClick={this.onClick}
          >
            <span className="slider-status">
              {active ? portalPhrases.get('portal.general.toggle_on') : portalPhrases.get('portal.general.toggle_off')}
            </span>
            <span className="slider-icon">
              <i className={classNames('fa', active ? 'fa-check' : 'fa-times')} />
            </span>
          </a>
          <span className="slider-label" onClick={this.onClick}>
            {label}
          </span>
        </div>
      </li>
    );
  }
}
