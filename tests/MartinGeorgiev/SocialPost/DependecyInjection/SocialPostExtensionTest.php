<?php

declare(strict_types=1);

namespace Tests\MartinGeorgiev\SocialPost\DependencyInjection;

use MartinGeorgiev\SocialPost\DependencyInjection\SocialPostExtension;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

/**
 * @since 1.0.0
 * @author Martin Georgiev <martin.georgiev@gmail.com>
 * @license https://opensource.org/licenses/MIT MIT
 * @link https://github.com/martin-georgiev/social-post-bundle Package's homepage
 * 
 * @covers MartinGeorgiev\SocialPost\DependecyInjection\Configuration
 * @covers MartinGeorgiev\SocialPost\DependecyInjection\SocialPostExtension
 */
class SocialPostExtensionTest extends PHPUnit_Framework_TestCase
{
    private function getConfigurationWithEmptyPublishOn(): array
    {
        $yaml = <<<EOF
social_post:
    publish_on: []
EOF;
        return (new Parser())->parse($yaml);
    }

    private function getConfigurationWithEmptyProvider(): array
    {
        $yaml = <<<EOF
social_post:
    publish_on: [twitter]
EOF;
        return (new Parser())->parse($yaml);
    }

    private function getMinimalConfiguration(): array
    {
        $yaml = <<<EOF
social_post:
    publish_on: [facebook, twitter]
    providers:
        facebook:
            app_id: "2017"
            app_secret: "some-secret"
            default_access_token: "some-access-token"
            page_id: "681"
        twitter:
            consumer_key: "some-consumer-key"
            consumer_secret: "some-consumer-secret"
            access_token: "some-access-token"
            access_token_secret: "some-access-token-secret"
EOF;
        return (new Parser())->parse($yaml);
    }

    private function getCompleteConfiguration(): array
    {
        $yaml = <<<EOF
social_post:
    publish_on: [facebook, twitter]
    providers:
        facebook:
            app_id: "2017"
            app_secret: "some-secret"
            default_access_token: "some-access-token"
            page_id: "681"
            enable_beta_mode: true
            default_graph_version: "v2.8"
            persistent_data_handler: "session"
            pseudo_random_string_generator: "mcrypt"
            http_client_handler: "guzzle"
        twitter:
            consumer_key: "some-consumer-key"
            consumer_secret: "some-consumer-secret"
            access_token: "some-access-token"
            access_token_secret: "some-access-token-secret"
EOF;
        return (new Parser())->parse($yaml);
    }

    /**
     * @param mixed $expectedParameterValue
     * @param string $containerParameter
     * @param ContainerBuilder $containerBuilder
     */
    private function assertContainerParameter($expectedParameterValue, string $containerParameter, ContainerBuilder $containerBuilder)
    {
        $this->assertSame(
            $expectedParameterValue,
            $containerBuilder->getParameter($containerParameter),
            sprintf('%s parameter is correct', $containerParameter)
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function test_will_throw_an_exception_when_no_value_for_publish_on()
    {
        $extension = new SocialPostExtension();
        $extension->load($this->getConfigurationWithEmptyPublishOn(), new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function test_will_throw_an_exception_when_no_provider_is_given()
    {
        $extension = new SocialPostExtension();
        $extension->load($this->getConfigurationWithEmptyProvider(), new ContainerBuilder());
    }

    public function test_facebook_defaults_whit_minimal_configuration()
    {
        $configs = $this->getMinimalConfiguration();
        $containerBuilder = new ContainerBuilder();
        $extension = new SocialPostExtension();
        $extension->load($configs, $containerBuilder);

        $facebookConfigurationWithDefaults = [
            'app_id' => '2017',
            'app_secret' => 'some-secret',
            'default_access_token' => 'some-access-token',
            'page_id' => '681',
            'enable_beta_mode' => false,
            'default_graph_version' => null,
            'persistent_data_handler' => 'memory',
            'pseudo_random_string_generator' => 'openssl',
            'http_client_handler' => 'curl',
        ];

        $this->assertContainerParameter($facebookConfigurationWithDefaults, 'social_post.configuration.facebook', $containerBuilder);
    }
    
    public function test_complete_configuration()
    {
        $configs = $this->getCompleteConfiguration();
        $containerBuilder = new ContainerBuilder();
        $extension = new SocialPostExtension();
        $extension->load($configs, $containerBuilder);

        $this->assertContainerParameter($configs['social_post']['publish_on'], 'social_post.configuration.publish_on', $containerBuilder);
        $this->assertContainerParameter($configs['social_post']['providers']['facebook'], 'social_post.configuration.facebook', $containerBuilder);
        $this->assertContainerParameter($configs['social_post']['providers']['facebook']['page_id'], 'social_post.configuration.facebook.page_id', $containerBuilder);
        $this->assertContainerParameter($configs['social_post']['providers']['twitter']['consumer_key'], 'social_post.configuration.twitter.consumer_key', $containerBuilder);
        $this->assertContainerParameter($configs['social_post']['providers']['twitter']['consumer_secret'], 'social_post.configuration.twitter.consumer_secret', $containerBuilder);
        $this->assertContainerParameter($configs['social_post']['providers']['twitter']['access_token'], 'social_post.configuration.twitter.access_token', $containerBuilder);
        $this->assertContainerParameter($configs['social_post']['providers']['twitter']['access_token_secret'], 'social_post.configuration.twitter.access_token_secret', $containerBuilder);
    }
}