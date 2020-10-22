import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import { FormattedDate, FormattedMessage } from 'react-intl';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import mobileMenu from '@deskpro/portal-style/dist/img/page-icons/menu.svg';
import classNames from 'classnames';
import moment from 'moment';
import Isvg from 'react-inlinesvg';
import $ from 'jquery';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import browserHistory from 'react-router/lib/browserHistory';
import Link from 'react-router/lib/Link';
import { PageList, Page, GuideSelector, CodeBlock } from '../index';

class ViewPage extends React.Component {
  static propTypes = {
    params: PropTypes.object
  };

  constructor(props) {
    super(props);
    let page = {};
    let loaded = true;
    if (window.page) {
      page = JSON.parse(window.page);
      loaded = true;
      page.content = this.addIdToh1(page.content, page.slug);
    }
    const pageList = JSON.parse(window.pageList);
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
      menuVisible:     false,
      flashes:         [],
      childrenPages:   page.children,
      guide,
      guideSlug,
      loaded,
      page,
      pageList,
      twoLevelSection: window.twoLevelSection,
    };
    if (window.page) {
      setTimeout(() => {
        this.changeInternalLinks();
        this.addCodeBlocksCopy();
        this.addGuideBlocks();
        this.addReactImageLazyload();
        this.setBreadCrumbs(page);
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
    window.addEventListener('resize', () => {
      if (!this.tickingResize) {
        window.requestAnimationFrame(() => {
          this.defineSizes();
          this.tickingResize = false;
        });
      }
      this.tickingResize = true;
    });
    this.defineSizes();
  }

  componentWillReceiveProps(nextProps) {
    // if (nextProps.params.slug !== this.props.params.slug) {
    //   this.grabPageFromApi(nextProps.params.slug);
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

  getPageSlug = (params) => {
    if (Object.prototype.hasOwnProperty.call(params, 'splat')) {
      return params.slug;
    }
    return '';
  }

  setBreadCrumbs = (page = null) => {
    const ol = document.querySelector('ol.breadcrumb');
    const { pageList, guideSlug, guide } = this.state;
    const hierarchy = [];
    if (page !== null) {
      hierarchy.push(page);
      let parentId = null;
      if (page.parent) {
        if (page.parent.id) {
          parentId = page.parent.id;
        } else {
          parentId = page.parent;
        }
      }
      while (parentId !== null) {
        // eslint-disable-next-line no-loop-func
        const parent = pageList.find(p => p.id === parentId);
        hierarchy.push(parent);
        parentId = parent.parent_id;
      }
    }
    let i = ol.childNodes.length;
    while (i > 0) {
      const el =  ol.childNodes[i - 1];
      if (el.className && el.className.indexOf('breadcrumbs-guide-root') !== -1) {
        el.classList.remove('active');
        break;
      }
      ol.removeChild(el);
      i -= 1;
    }
    hierarchy.push({
      title: guide.title,
      guide: true,
    });
    hierarchy.reverse();
    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }
    let li;
    hierarchy.forEach((item) => {
      li = document.createElement('li');
      li.className = 'breadcrumb-item';
      const icon = document.createElement('i');
      icon.className = 'dp-po-icon fal fa-angle-right';
      li.appendChild(icon);
      const link = document.createElement('a');
      link.className = 'dp-po-Breadcrumb-link';
      link.href = '#';
      link.title = item.title;
      link.text = item.title;

      const path = `${baseUrl}/guides/${guideSlug}/${item.slug}`;
      if (item.no_content !== '1' && !item.guide) {
        link.href = item.slug;
        link.onclick = (e) => {
          e.preventDefault();
          browserHistory.push(path);
          this.grabPageFromApi(item.slug);
        };
      } else {
        if (item.guide) {
          link.href = guideSlug;
        }
        link.onclick = (e) => {
          e.preventDefault();
          browserHistory.push(`${baseUrl}/guides/${guideSlug}`);
          this.selectGuide(guide);
        };
      }
      li.appendChild(link);
      ol.appendChild(li);
    });
    li.classList.add('active');

    const toolOptions = {
      template: '<div class="tooltip dp-po-tip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
    };
    $('[data-toggle="tooltip"]').tooltip(toolOptions);
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
    if (!this.state.fixed) {
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
        console.log(internalLink);
        newLink.innerHTML = `<i class="fas fa-book"></i> ${internalLink.text}`;
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
      icon = 'info-circle';
    } else {
      mode = 'warning';
      title = 'Warning';
      icon = 'exclamation-circle';
    }
    const codeBlock = (
      <div className={`dp-po-post-content-${mode}`} >
        <h4 className={`dp-po-post-content-${mode}-title`}>
          <FontAwesomeIcon icon={['fal', icon]} className="dp-po-icon" /> {title}
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

        const pageList = response.data.data;
        this.setState({
          pageList,
        });
      });
    } else {
      const pageSlug = path.replace(/^(\/[^/]+)?\/guides\/.*\//, '');
      this.grabPageFromApi(pageSlug);
    }
    browserHistory.push(path);
    return false;
  };

  grabPageFromApi = (slug, forceScroll = false) => {
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
      loaded:      false,
      menuVisible: false,
    });

    portalHttp.sendGet(`DP_URL/portal/api/guides/topic/${slug}`).then((response) => {
      if (response.isError()) {
        return;
      }


      const page = response.data.data;
      page.content = this.addIdToh1(page.content, page.slug);

      this.setBreadCrumbs(page);
      this.setState({
        loaded:  true,
        page,
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
      this.setState({
        childrenPages: []
      });
      if (page.children && page.children.length) {
        portalHttp.sendGet(`DP_URL/portal/api/guides/topic_children/${slug}`).then((childrenResponse) => {
          this.setState({
            childrenPages: childrenResponse.data.data
          });
        });
      }
    });
  };

  postComment = comment => new Promise(
    (resolve, reject) => portalHttp.sendPost(
      `DP_URL/portal/api/guides/topic/${this.state.page.slug}/comment`,
      comment
    ).then((response) => {
      if (response.data) {
        if (response.data.data.comment) {
          this.state.page.calc_num_comments = this.state.page.calc_num_comments + 1;
          this.state.page.comments.push(response.data.data.comment);
          this.setState({
            page: this.state.page
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

  toggleMenu = () => {
    this.setState({
      menuVisible: !this.state.menuVisible
    });
  }

  selectGuide = (guide) => {
    portalHttp.sendGet(`DP_URL/portal/api/guides/topics/${guide.slug}`).then((response) => {
      if (response.isError()) {
        return;
      }

      const pageList = response.data.data;
      this.setState({
        pageList,
        guide,
        guideSlug:       guide.slug,
        twoLevelSection: guide.two_level_section,
      });
      // const page = Object.values(pageList).filter(t => t.no_content === '0' && t.content_length !== '0').shift();
      //
      // this.grabPageFromApi(page.slug);
      this.setState({
        page: {}
      });

      let baseUrl = window.DESKPRO_BASE_URL;
      if (baseUrl) {
        baseUrl = baseUrl.replace(/\/+$/, '');
      }

      browserHistory.push(`${baseUrl}/guides/${guide.slug}`);
      this.setBreadCrumbs();
      // if (Object.values(page.children).length) {
      //   const child = Object.values(page.children).sort(
      //     (a, b) => parseInt(a.display_order, 10) - parseInt(b.display_order, 10)
      //   ).shift();
      //   browserHistory.push(`${baseUrl}/guides/${guide.slug}/${page.slug}/${child.slug}`);
      // } else {
      //   browserHistory.push(`${baseUrl}/guides/${guide.slug}/${page.slug}`);
      // }
      window.scrollTo(0, 0);
    });
  };

  renderGuideLanding() {
    const { guide, pageList, guideSlug } = this.state;
    let { description } = guide;
    const { splash_image_property: splashImageProperty } = guide;
    const pages = Object.values(pageList).filter(t => t.no_content === '0' && t.content_length !== '0');
    let page1 = pages.shift();

    if (!page1) {
      page1 = Object.values(pageList).filter(t => t.no_content === '0').shift();
    }

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    if (!description) {
      const page2 = pages.shift();
      if (page2) {
        description = (
          <FormattedMessage
            id="helpcenter.guides.default_description"
            values={{
              guide_name: guide.title,
              page1:      page1.title,
              page2:      page2.title,
            }}
          />
        );
      } else if (page1) {
        description = (
          <FormattedMessage
            id="helpcenter.guides.default_description_short"
            values={{
              guide_name: guide.title,
              page1:      page1.title,
            }}
          />
        );
      } else {
        description = null;
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
        <div className="col-md-9">
          <div className="dp-po-guides-landing">
            {splashImage}
            <div className="dp-po-guides-landing-title">
              <h1>{guide.title}</h1>
              <div className="dp-po-guides-meta">
                {guide.date_published && <span><FormattedMessage id="helpcenter.general.published" />: <strong><FormattedDate value={datePublished} day="numeric" month="short" year="numeric" /></strong></span>}
                {guide.date_published && guide.date_updated && <span className="separator">|</span>}
                {guide.date_updated && <span><FormattedMessage id="helpcenter.general.last_updated" />: <strong><FormattedDate value={dateUpdated} day="numeric" month="short" year="numeric" /></strong></span>}
              </div>
            </div>
            <div className="dp-po-guides-landing-body">
              <p>{description}</p>
              <Link
                className="dp-po-guides-btn btn btn-outline-primary"
                to={`${baseUrl}/guides/${guideSlug}/${page1.slug}`}
                onClick={() => {
                  this.grabPageFromApi(page1.slug);
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

  renderPage() {
    const { page, childrenPages, guideSlug, pageSlug, loaded, pageList, flashes } = this.state;
    if (page.id) {
      return (
        <Page
          page={page}
          childrenPages={childrenPages}
          pageList={pageList}
          guideSlug={guideSlug}
          pageSlug={pageSlug}
          flashes={flashes}
          data={page}
          sizes={this.sizes}
          loaded={loaded}
          postComment={this.postComment}
          grabPageFromApi={this.grabPageFromApi}
        />
      );
    }
    if (loaded) {
      return this.renderGuideLanding();
    }
    return null;
  }

  render() {
    const { pageList, fixed, loaded, twoLevelSection, guide, menuVisible } = this.state;
    const guideSlug = this.getGuideSlug(this.props.params);
    const pageSlug = this.getPageSlug(this.props.params);

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
              <div className="d-block d-md-none dp-po-guides-mobile-menu">
                <a className="menu" onClick={this.toggleMenu}>
                  <Isvg src={mobileMenu} />
                </a>
              </div>
              <div className="row">
                <div className={classNames('col-md-3 d-none d-md-block page-list', { 'mobile-visible': menuVisible })}>
                  <PageList
                    pages={pageList}
                    guideSlug={guideSlug}
                    pageSlug={pageSlug}
                    guide={guide}
                    sizes={this.sizes}
                    fixed={fixed}
                    grabPageFromApi={this.grabPageFromApi}
                    toggleMenu={this.toggleMenu}
                    twoLevelSection={twoLevelSection}
                  />
                </div>
                <div className="col-md-9">
                  { loaded ||
                    <div className="row">
                      <div className="col-md-9">
                        <div className={classNames({ 'dp-po-guides-loading': !loaded })}>
                          <FontAwesomeIcon icon={['far', 'spinner']} pulse size="3x" className="dp-icon" />
                        </div>
                      </div>
                    </div>
                  }
                  <div className="dp-po-guides-block">
                    {this.renderPage()}
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

export default ViewPage;
