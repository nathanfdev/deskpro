import React from 'react';
import PropTypes from 'prop-types';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

export const IconRenderer = ({
  object,
  default: defaultReturn,
  className,
  figureClassName = '',
  figureStyle,
}) => {
  const iconProperty = object.icon_property;

  if (!iconProperty) {
    return (
      <figure className={`dp-po-icon ${figureClassName}`} style={figureStyle}>
        {defaultReturn}
      </figure>
    );
  }

  if (iconProperty.urn_ns === 'urn:deskpro:local:blobs') {
    return (
      <figure className={`dp-po-icon ${figureClassName}`}>
        <span className={className}>
          <img src={iconProperty.url} alt="icon" />
        </span>
      </figure>
    );
  } else if (iconProperty.urn_ns === 'urn:deskpro:product:icons:fontawesome') {
    const style = {};
    if (object.color && typeof style.backgroundColor === 'undefined') {
      style.backgroundColor = `#${object.color}`;
    }

    let iconStyle = 'fas';
    if (typeof iconProperty.options.style !== 'undefined') {
      iconStyle = iconProperty.options.style;
    }

    if (iconStyle) {
      return (
        <figure className={`dp-po-icon ${figureClassName}`} style={figureStyle}>
          <FontAwesomeIcon icon={[iconStyle, iconProperty.urn_path.replace(/^fa-/, '')]} style={style} className={className} />
        </figure>
      );
    }
  }

  return (
    <figure className={`dp-po-icon ${figureClassName}`}>
      {defaultReturn}
    </figure>
  );
};
IconRenderer.propTypes = {
  object:          PropTypes.object,
  default:         PropTypes.node,
  className:       PropTypes.string,
  figureClassName: PropTypes.string,
  figureStyle:     PropTypes.object,
};
IconRenderer.defaultProps = {
  className:       'dp-po-icon',
  figureClassName: '',
  figureStyle:     {}
};
