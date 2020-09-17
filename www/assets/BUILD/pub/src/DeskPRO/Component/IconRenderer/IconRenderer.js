import React from 'react';
import PropTypes from 'prop-types';
// import * as fas from '@fortawesome/pro-solid-svg-icons';
// import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

export class IconRenderer extends React.Component {
  static propTypes = {
    object:    PropTypes.object,
    default:   PropTypes.node,
    className: PropTypes.string,
  };

  static defaultProps = {
    className: 'dp-po-icon'
  }

  render() {
    const { object, className } = this.props;

    const iconProperty = object.icon_property;

    if (!iconProperty) {
      return this.props.default;
    }

    if (iconProperty.urn_ns === 'urn:deskpro:local:blobs') {
      return (
        <span className={className}>
          <img src={iconProperty.url} alt="icon" />
        </span>
      );
    } else if (iconProperty.urn_ns === 'urn:deskpro:product:icons:fontawesome') {
      // const iconClass = iconProperty.urn_path.replace(/(-)(.)/g, function(match, $1, $2) { return $2.toUpperCase(); })
      const style = {};
      if (object.color) {
        style.backgroundColor = `#${object.color}`;
      }

      // if (fas[iconClass]) {
      //   return <FontAwesomeIcon className={className} icon={fas[iconClass]} />;
      // }
      // console.log(iconProperty.urn_path);
      let iconStyle = 'fas';
      if (typeof iconProperty.options.style !== 'undefined') {
        iconStyle = iconProperty.options.style;
      }

      if (iconStyle) {
        return (
          <i className={`${className} ${iconStyle} ${iconProperty.urn_path}`} style={style} />
        );
      }
    }

    return this.props.default;
  }
}
