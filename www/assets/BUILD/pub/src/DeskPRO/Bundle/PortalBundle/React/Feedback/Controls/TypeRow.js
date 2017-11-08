import PropTypes from 'prop-types';
import React from 'react';
import includes from 'lodash/includes';
import map from 'lodash/map';
import { TypeSlider } from './TypeSlider';

export class TypeRow extends React.Component {
  static propTypes = {
    available:  PropTypes.object,
    selected:   PropTypes.array,
    toggleType: PropTypes.func
  };

  render() {
    return (
      <div className="types">
        <ul className="slider-list">
          {map(this.props.available, (type, typeId) =>
            <TypeSlider
              key={typeId}
              label={type}
              id={typeId}
              active={includes(this.props.selected, parseInt(typeId, 10))}
              toggleType={this.props.toggleType}
            />
          )}
        </ul>
      </div>
    );
  }
}
