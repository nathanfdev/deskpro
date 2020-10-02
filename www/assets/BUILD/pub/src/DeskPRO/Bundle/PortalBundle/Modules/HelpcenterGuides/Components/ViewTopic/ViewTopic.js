import PropTypes from 'prop-types';
import React, { Fragment } from 'react';
import ReactDOM from 'react-dom';
import { FormattedDate, FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import moment from 'moment';
import $ from 'jquery';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import browserHistory from 'react-router/lib/browserHistory';
import Link from 'react-router/lib/Link';
import { TopicList, Topic, GuideSelector, CodeBlock } from '../index';

class ViewTopic extends React.Component {
  static propTypes = {
    params: PropTypes.object
  };

  constructor(props) {
    super(props);
    let topic = {};
    let loaded = true;
    if (window.topic) {
      topic = JSON.parse(window.topic);
      loaded = true;
      topic.content = this.addIdToh1(topic.content, topic.slug);
    }
    const topicList = JSON.parse(window.topicList);
    let guides = [];
    if (window.guides) {
      guides = JSON.parse(window.guides);
    }
    if (!Array.isArray(guides)) {
      guides = Object.values(guides);
    }
    const guideSlug = this.getGuideSlug(this.props.params);
    const guide = guides.find(g => g.slug === guideSlug);
    this.state = {
      fixed:           false,
      doSpin:          false,
      flashes:         [],
      guide,
      guideSlug,
      loaded,
      topic,
      topicList,
      twoLevelSection: window.twoLevelSection,
    };
    if (window.topic) {
      setTimeout(() => {
        this.changeInternalLinks();
        this.addCodeBlocksCopy();
        this.addGuideBlocks();
        this.addReactImageLazyload();
      }, 500);
    }
    this.contentChanged = false;
    this.ticking = false;
    this.targetSlug = props.params.slug;
    window.onload = this.defineSizes;
    moment.locale(window.DESKPRO_LOCALE);
  }

  componentDidMount() {
    this.changeInternalLinks();
    this.addCodeBlocksCopy();
    this.addGuideBlocks();
    window.addEventListener('scroll', () => {
      if (!this.ticking) {
        window.requestAnimationFrame(() => {
          this.handleScroll();
          this.ticking = false;
        });
      }
      this.ticking = true;
    });
    window.addEventListener('resize', this.defineSizes);
    this.defineSizes();
  }

  componentWillReceiveProps(nextProps) {
    // if (nextProps.params.slug !== this.props.params.slug) {
    //   this.grabTopicFromApi(nextProps.params.slug);
    //   this.contentChanged = true;
    // }
    const nextGuideSlug = this.getGuideSlug(nextProps.params);
    if (nextGuideSlug !== this.getGuideSlug(this.props.params)) {
      this.setState({
        guideSlug: nextGuideSlug
      });
    }
  }

  componentDidUpdate() {
    if (this.contentChanged) {
      this.contentChanged = false;
      this.defineSizes();
    }
    const toolOptions = {
      template: '<div class="tooltip dp-po-tip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
    };

    $('[data-toggle="tooltip"]').tooltip(toolOptions);
  }

  componentWillUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
    window.addEventListener('resize', this.defineSizes);
  }

  getGuideSlug = (params) => {
    if (Object.prototype.hasOwnProperty.call(params, 'splat')) {
      return params.splat.split('/')[0];
    }
    return params.slug;
  }

  getTopicSlug = (params) => {
    if (Object.prototype.hasOwnProperty.call(params, 'splat')) {
      return params.slug;
    }
    return '';
  }

  handleScroll = () => {
    if (this.state.fixed !== this.elements.guidesMain.getBoundingClientRect().top < 28) {
      this.setState({
        fixed: this.elements.guidesMain.getBoundingClientRect().top < 28,
      });
    }
  };

  defineSizes = () => {
    if (!this.elements) {
      this.elements = {
        guidesMain:   window.document.getElementsByClassName('dp-po-guides-wrap')[0],
        search:       window.document.getElementsByClassName('dp-po-guides-search')[0],
        articleRight: window.document.getElementsByClassName('dp-po-guides-block-article-right')[0],
      };
    }
    if (!this.sizes) {
      this.sizes = {
        topMargin:    this.elements.guidesMain && this.elements.guidesMain.getBoundingClientRect().top - document.documentElement.scrollTop,
        searchWidth:  this.elements.search && this.elements.search.getBoundingClientRect().width,
        articleWidth: this.elements.articleRight && this.elements.articleRight.getBoundingClientRect().width,
      };
    }
  };

  changeInternalLinks = () => {
    const links = document.querySelectorAll('a.internal_link.topic');
    Array.prototype.forEach.call(links, (internalLink) => {
      let target = internalLink.pathname;
      const guideSlug = target.replace(/^(\/[^/]+)?\/guides\//, '').replace(/\/.*/, '');
      if (true || guideSlug !== this.state.guideSlug) {
        const newLink = document.createElement('a');
        newLink.className = 'internal_link topic';
        newLink.onclick = e => this.internalLink(e, target);
        newLink.href = '#';
        if (internalLink.hash) {
          target += internalLink.hash;
        }
        newLink.innerText = internalLink.text;
        internalLink.parentNode.replaceChild(newLink, internalLink);
      } else {
        const newLink = document.createElement('span');
        const link = (
          <Link
            to={internalLink.pathname}
            className="internal_link topic"
            onClick={this.handleClick}
          >
            {internalLink.text}
          </Link>
        );
        ReactDOM.render(link, newLink, () => {
          internalLink.parentNode.replaceChild(newLink, internalLink);
        });
      }
    });
  };

  addGuideBlocks = () => {
    const blocks = document.querySelectorAll('.block.info,.block.warning');
    Array.prototype.forEach.call(blocks, this.addGuideBlock);
  };

  addGuideBlock = (block) => {
    if (block.querySelector('h4')) {
      return;
    }
    let mode;
    let title;
    let icon;
    if (block.classList.contains('info')) {
      mode = 'note';
      title = 'Note';
      icon = 'fa-info-circle';
    } else {
      mode = 'warning';
      title = 'Warning';
      icon = 'fa-exclamation-circle';
    }
    const codeBlock = (
      <div className={`dp-po-post-content-${mode}`} >
        <h4 className={`dp-po-post-content-${mode}-title`}>
          <i className={classNames('dp-po-icon', 'fal', icon)} /> {title}
        </h4>
        <p dangerouslySetInnerHTML={{ __html: block.innerHTML }} />
      </div>
    );
    ReactDOM.render(codeBlock, block);
  };

  addCodeBlocksCopy = () => {
    const blocks = document.querySelectorAll('pre code');
    Array.prototype.forEach.call(blocks, this.addCodeBlockCopy);
  };

  addCodeBlockCopy = (block) => {
    if (block.innerText) {
      const codeBlock = <CodeBlock text={block.innerText} html={block.innerHTML} />;
      ReactDOM.render(codeBlock, block);
    } else {
      setTimeout(() => { this.addCodeBlockCopy(block); }, 500);
    }
  };

  addReactImageLazyload = () => {
    const guideBlock = document.getElementsByClassName('dp-po-guides-block')[0];
    const images = guideBlock.querySelectorAll('.dp-po-guides-block-content img');
    if ('IntersectionObserver' in window) {
      const lazyImageObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const lazyImage = entry.target;
            if (lazyImage.dataset.src) {
              lazyImage.src = lazyImage.dataset.src;
              // lazyImage.srcset = lazyImage.dataset.srcset;
              lazyImage.classList.remove('lazy');
            }
            lazyImageObserver.unobserve(lazyImage);
          }
        });
      });

      images.forEach((lazyImage) => {
        if (!lazyImage.attributes.width && (lazyImage.dataset.width || lazyImage.dataset.with)) {
          if (lazyImage.dataset.with) {
            lazyImage.setAttribute('width', lazyImage.dataset.with);
          } else if (lazyImage.dataset.width) {
            lazyImage.setAttribute('width', lazyImage.dataset.width);
          }
        }
        lazyImageObserver.observe(lazyImage);
      });
    }
  };

  addIdToh1 = (html, slug) => {
    const container = document.createElement('div');
    container.innerHTML = html;

    Array.from(container.querySelectorAll('h1')).forEach((h1) => {
      const newH1 = document.createElement('h1');
      newH1.innerText = `${h1.innerText} `;
      newH1.id = `${slug}_${h1.innerText.toLowerCase().replace(/[():]/g, '').replace(/ /g, '-')}`;
      newH1.className = 'anchor';
      container.replaceChild(newH1, h1);
    });

    return container.innerHTML;
  };

  internalLink = (e, path) => {
    e.preventDefault();
    const guideSlug = path.replace(/^(\/[^/]+)?\/guides\//, '').replace(/\/.*/, '');
    if (guideSlug !== this.state.guideSlug) {
      portalHttp.sendGet(`DP_URL/portal/api/guides/topics/${guideSlug}`).then((response) => {
        if (response.isError()) {
          return;
        }

        const topicList = response.data.data;
        this.setState({
          topicList,
        });
      });
    } else {
      const topicSlug = path.replace(/^(\/[^/]+)?\/guides\/.*\//, '');
      this.grabTopicFromApi(topicSlug);
    }
    browserHistory.push(path);
    return false;
  };

  grabTopicFromApi = (slug, forceScroll = false) => {
    if (this.scrolling) {
      return;
    }

    let scroll = false;
    let scrollPage = false;
    if (this.elements.guidesMain.getBoundingClientRect().top <= 70) {
      scroll = true;
    }
    scrollPage = this.elements.guidesMain.getBoundingClientRect().top + window.document.documentElement.scrollTop;

    this.setState({
      loaded: false
    });

    portalHttp.sendGet(`DP_URL/portal/api/guides/topic/${slug}?inline_sideloads=true&include=topic`).then((response) => {
      if (response.isError()) {
        return;
      }


      const topic = response.data.data;
      topic.content = this.addIdToh1(topic.content, topic.slug);
      this.setState({
        loaded:  true,
        topic,
        flashes: [],
      });
      if (forceScroll || scroll) {
        setTimeout(() => {
          window.scrollTo(0, scrollPage - 27);
        }, 200);
      }
      this.changeInternalLinks();
      this.addCodeBlocksCopy();
      this.addGuideBlocks();
      this.addReactImageLazyload();
    });
  };

  postComment = comment => new Promise(
    (resolve, reject) => portalHttp.sendPost(
      `DP_URL/portal/api/guides/topic/${this.state.topic.slug}/comment`,
      comment
    ).then((response) => {
      if (response.data) {
        if (response.data.data.comment) {
          this.state.topic.calc_num_comments = this.state.topic.calc_num_comments + 1;
          this.state.topic.comments.push(response.data.data.comment);
          this.setState({
            topic: this.state.topic
          });
        }
        if (response.data.data.flashes) {
          this.setState({
            flashes: response.data.data.flashes
          });
        }
        if (response.data.data.errors) {
          if (response.data.data.errors.form.errors) {
            Array.prototype.forEach.call(response.data.data.errors.form.errors, (error) => {
              this.state.flashes.push({
                type:    'error',
                message: error
              });
            });
            this.setState({
              flashes: this.state.flashes
            });
          }
          return reject(response);
        }
        return resolve(response);
      }
      return reject(response);
    })
  );

  selectGuide = (guide) => {
    portalHttp.sendGet(`DP_URL/portal/api/guides/topics/${guide.slug}`).then((response) => {
      if (response.isError()) {
        return;
      }

      const topicList = response.data.data;
      this.setState({
        topicList,
        guide,
        guideSlug:       guide.slug,
        twoLevelSection: guide.two_level_section,
      });
      // const topic = Object.values(topicList).filter(t => t.no_content === '0' && t.content_length !== '0').shift();
      //
      // this.grabTopicFromApi(topic.slug);
      this.setState({
        topic: {}
      });

      let baseUrl = window.DESKPRO_BASE_URL;
      if (baseUrl) {
        baseUrl = baseUrl.replace(/\/+$/, '');
      }

      browserHistory.push(`${baseUrl}/guides/${guide.slug}`);
      // if (Object.values(topic.children).length) {
      //   const child = Object.values(topic.children).sort(
      //     (a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10)
      //   ).shift();
      //   browserHistory.push(`${baseUrl}/guides/${guide.slug}/${topic.slug}/${child.slug}`);
      // } else {
      //   browserHistory.push(`${baseUrl}/guides/${guide.slug}/${topic.slug}`);
      // }
      window.scrollTo(0, 0);
    });
  };

  renderGuideLanding() {
    const { guide, topicList, guideSlug } = this.state;
    let { description } = guide;
    const { splash_image_property: splashImageProperty } = guide;
    const topics = Object.values(topicList).filter(t => t.no_content === '0' && t.content_length !== '0');
    const topic1 = topics.shift();

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    if (!description) {
      const topic2 = topics.shift();
      if (topic2) {
        description = (
          <FormattedMessage
            id="helpcenter.guides.default_description"
            values={{
              guide_name: guide.title,
              topic1:     topic1.title,
              topic2:     topic2.title,
            }}
          />
        );
      } else {
        description = (
          <FormattedMessage
            id="helpcenter.guides.default_description_short"
            values={{
              guide_name: guide.title,
              topic1:     topic1.title,
            }}
          />
        );
      }
    }
    let splashImage = null;
    if (splashImageProperty) {
      if (splashImageProperty.url) {
        splashImage = (<img className="dp-po-guides-landing-splash" src={splashImageProperty.url} alt="" />);
      } else {
        splashImage = (<img className="dp-po-guides-landing-splash" src={`${splashImageProperty.options.url}&w=900`} alt="" />);
      }
    }
    const datePublished = guide.date_published ? guide.date_published.replace(/T.*/, '').replace(/-/g, '/') : null;
    const dateUpdated = guide.date_updated ? guide.date_updated.replace(/T.*/, '').replace(/-/g, '/') : null;
    return (
      <div className="row">
        <div className="col-sm-9">
          <div className="dp-po-guides-landing">
            {splashImage}
            <div className="dp-po-guides-landing-title">
              <h1>{guide.title}</h1>
              <div className="dp-po-guides-meta">
                {guide.date_published && <Fragment><FormattedMessage id="helpcenter.general.published" />: <strong><FormattedDate value={datePublished} day="numeric" month="short" year="numeric" /></strong></Fragment>}
                {guide.date_published && guide.date_updated && <span className="separator">|</span>}
                {guide.date_updated && <Fragment><FormattedMessage id="helpcenter.general.last_updated" />: <strong><FormattedDate value={dateUpdated} day="numeric" month="short" year="numeric" /></strong></Fragment>}
              </div>
            </div>
            <div className="dp-po-guides-landing-body">
              <p>{description}</p>
              <Link
                className="dp-po-guides-btn btn btn-outline-primary"
                to={`${baseUrl}/guides/${guideSlug}/${topic1.slug}`}
                onClick={() => {
                  this.grabTopicFromApi(topic1.slug);
                }}
              >
                <FormattedMessage id="helpcenter.guides.start_reading" />
              </Link>
            </div>
          </div>
        </div>
      </div>
    );
  }

  renderTopic() {
    const { topic, guideSlug, topicSlug, loaded, topicList, flashes } = this.state;
    if (topic.id) {
      return (
        <Topic
          topic={topic}
          topicList={topicList}
          guideSlug={guideSlug}
          topicSlug={topicSlug}
          flashes={flashes}
          data={topic}
          sizes={this.sizes}
          loaded={loaded}
          postComment={this.postComment}
          grabTopicFromApi={this.grabTopicFromApi}
        />
      );
    }
    if (loaded) {
      return this.renderGuideLanding();
    }
    return null;
  }

  render() {
    const { topicList, fixed, loaded, twoLevelSection, guide } = this.state;
    const guideSlug = this.getGuideSlug(this.props.params);
    const topicSlug = this.getTopicSlug(this.props.params);

    return (
      <div className={classNames('container', { fixed })}>
        <GuideSelector
          guideSlug={this.state.guideSlug}
          selectGuide={this.selectGuide}
          fixed={fixed}
        />
        <div className={classNames('dp-po-guides-section')}>
          <div className="dp-po-guides-wrap">
            <div className="container-fluid">
              <div className="row">
                <div className="col-sm-3">
                  <TopicList
                    topics={topicList}
                    guideSlug={guideSlug}
                    topicSlug={topicSlug}
                    guide={guide}
                    grabTopicFromApi={this.grabTopicFromApi}
                    sizes={this.sizes}
                    twoLevelSection={twoLevelSection}
                  />
                </div>
                <div className="col-sm-9">
                  { loaded ||
                    <div className="row">
                      <div className="col-sm-9">
                        <div className={classNames({ 'dp-po-guides-loading': !loaded })}>
                          <i className="dp-icon fa-3x far fa-spinner fa-pulse" />
                        </div>
                      </div>
                    </div>
                  }
                  <div className="dp-po-guides-block">
                    {this.renderTopic()}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default ViewTopic;
