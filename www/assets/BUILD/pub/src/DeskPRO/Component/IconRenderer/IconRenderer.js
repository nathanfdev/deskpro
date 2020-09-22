import React from 'react';
import PropTypes from 'prop-types';
// import * as fas from '@fortawesome/pro-solid-svg-icons';
// import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

export const IconRenderer = ({
  object,
  default: defaultReturn,
  className,
  figureStyle,
}) => {
  const iconProperty = object.icon_property;

  if (!iconProperty) {
    return (
      <figure className="dp-po-icon" style={figureStyle}>
        {defaultReturn}
      </figure>
    );
  }

  if (iconProperty.urn_ns === 'urn:deskpro:local:blobs') {
    return (
      <figure className="dp-po-icon">
        <span className={className}>
          <img src={iconProperty.url} alt="icon" />
        </span>
      </figure>
    );
  } else if (iconProperty.urn_ns === 'urn:deskpro:product:icons:fontawesome') {
    // const iconClass = iconProperty.urn_path.replace(/(-)(.)/g, function(match, $1, $2) { return $2.toUpperCase(); })
    const style = {};
    if (object.color && typeof style.backgroundColor === 'undefined') {
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
        <figure className="dp-po-icon" style={figureStyle}>
          <i className={`${className} ${iconStyle} ${iconProperty.urn_path}`} style={style} />
        </figure>
      );
    }
  }

  return (
    <figure className="dp-po-icon">
      {defaultReturn}
    </figure>
  );
};
IconRenderer.propTypes = {
  object:      PropTypes.object,
  default:     PropTypes.node,
  className:   PropTypes.string,
  figureStyle: PropTypes.object,
};
IconRenderer.defaultProps = {
  className:   'dp-po-icon',
  figureStyle: {}
};
