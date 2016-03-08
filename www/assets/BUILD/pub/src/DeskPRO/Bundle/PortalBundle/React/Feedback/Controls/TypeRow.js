import React from 'react';
import { TypeSlider } from './TypeSlider';
import { portalUrlGenerator } from '../../../Http/PortalUrlGenerator';
import _ from 'lodash';

export class TypeRow extends React.Component {

  render() {
    return (
      <div className="types">
        <ul className="slider-list">
          {_.map(this.props.available, (type, type_id) => {
            return (
              <TypeSlider key={type_id} label={type} id={type_id}
                          active={_.includes(this.props.selected, _.parseInt(type_id))}
                          toggleType={this.props.toggleType}/>
            );
          })}
          <li className="float-right">
            <img style={{display: this.props.doSpin ? "inline" : "none", height: "30px", width: "30px"}}
                 src={ portalUrlGenerator.getSpinnerPath() } />
          </li>
        </ul>
      </div>
    );
  }
}
