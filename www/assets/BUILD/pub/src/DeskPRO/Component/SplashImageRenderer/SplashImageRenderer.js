import React from 'react';
import PropTypes from 'prop-types';

export class SplashImageRenderer extends React.PureComponent {
  static propTypes = {
    object:    PropTypes.object,
    width:     PropTypes.number,
    className: PropTypes.string,
  };

  static defaultProps = {
    width: 360,
  };

  render() {
    const { object, className, width } = this.props;

    const splashImage = object.splash_image_property;

    if (!splashImage) {
      return null;
    }

    if (splashImage.urn_ns === 'urn:deskpro:local:blobs') {
      return (
        <span className="dp-po-icon">
          <img src={splashImage.url} alt="icon" />
        </span>
      );
    } else if (splashImage.urn_ns === 'urn:deskpro:product:splash:unsplash' && splashImage.options) {
      const style = { backgroundImage: `url('${splashImage.options.url}&w=${width}')` };

      if (style) {
        return (
          <figure className={className} style={style} />
        );
      }
    }

    return null;
  }
}
