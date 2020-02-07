import React from 'react';
import PropTypes from 'prop-types';

export class IconRenderer extends React.PureComponent {
  static propTypes = {
    object:  PropTypes.object,
    default: PropTypes.node,
  };

  render() {
    const { object } = this.props;

    const iconProperty = object.icon_property;

    if (!iconProperty) {
      return this.props.default;
    }

    if (iconProperty.urn_ns === 'urn:deskpro:local:blobs') {
      return (
        <span className="dp-po-icon">
          <img src={iconProperty.url} alt="icon" />
        </span>
      );
    } else if (iconProperty.urn_ns === 'urn:deskpro:product:icons:fontawesome') {
      let iconStyle = 'fas';
      if (typeof iconProperty.options.style !== 'undefined') {
        iconStyle = iconProperty.options.style;
      }
      const style = {};
      if (object.color) {
        style.backgroundColor = `#${object.color}`;
      }

      if (iconStyle) {
        return (
          <i className={`dp-po-icon ${iconStyle} ${iconProperty.urn_path}`} style={style} />
        );
      }
    }

    return this.props.default;
  }
}
