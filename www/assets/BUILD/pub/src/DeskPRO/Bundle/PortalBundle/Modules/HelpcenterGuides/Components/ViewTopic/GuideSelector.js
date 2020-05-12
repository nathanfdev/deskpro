import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { IconRenderer } from 'DeskPRO/Component/IconRenderer';
import { SplashImageRenderer } from 'DeskPRO/Component/SplashImageRenderer';
import $ from 'jquery';

class GuideSelector extends React.Component {
  static propTypes = {
    guideSlug:   PropTypes.string,
    selectGuide: PropTypes.func,
    fixed:       PropTypes.bool,
  };

  constructor(props) {
    super(props);
    let guides = [];
    if (window.guides) {
      guides = JSON.parse(window.guides);
    }
    if (!Array.isArray(guides)) {
      guides = Object.values(guides);
    }
    this.state = {
      guides
    };
  }

  componentDidMount() {
    import('slick-carousel').then(() => {
      $('.dp-po-guides-carousel-list').slick({
        dots:           false,
        infinite:       false,
        speed:          300,
        slidesToShow:   5,
        slidesToScroll: 5,
        rows:           0,
        nextArrow:      '<button class="dp-po-guides-carousel-arrow dp-po-guides-carousel-arrow-right"><i class="dp-po-icon far fa-angle-right"></i></button>',
        prevArrow:      '<button class="dp-po-guides-carousel-arrow dp-po-guides-carousel-arrow-left"><i class="dp-po-icon far fa-angle-left"></i></button>',
        responsive:     [{
          breakpoint: 768,
          settings:   {
            slidesToShow:   1,
            slidesToScroll: 1,
            infinite:       true,
            dots:           true,
            arrows:         false,
          }
        }]
      });
    })
  }

  onClickGuide = (e, guide) => {
    e.preventDefault();
    this.props.selectGuide(guide);
  };

  renderGuide = (guide, activeGuide, baseUrl, withSplash) => (
    <div className={classNames('dp-po-guides-carousel-item', { active: guide.id === activeGuide.id })} key={guide.id}>
      {withSplash && guide.splash_image_property ? <SplashImageRenderer className="dp-po-guides-carousel-image" object={guide} />: null}
      <div className="dp-po-guides-carousel-content">
        <a href={`${baseUrl}/guides/${guide.slug}`} className="dp-po-guides-carousel-link" onClick={e => this.onClickGuide(e, guide)}>
          <figure className="dp-po-icon"><IconRenderer object={guide} default={<i className="fal fa-user-headset" />} /></figure> {guide.title}
        </a>
      </div>
    </div>
    );

  render() {
    const { guides } = this.state;
    const { fixed } = this.props;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    const activeGuide = guides.filter(g => g.slug === this.props.guideSlug)[0];

    const withSplash = guides.length > 2;

    return (
      <div className={classNames('dp-po-guides-carousel', { fixed })}>
        <div className="dp-po-guides-carousel-list">
          {guides.map(guide => this.renderGuide(guide, activeGuide, baseUrl, withSplash))}
        </div>
        <div className="dp-po-carousel-shadow" />
      </div>
    );
  }
}
export default GuideSelector;
